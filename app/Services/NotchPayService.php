<?php

namespace App\Services;

use NotchPay\Facades\NotchPay;
use Illuminate\Http\Request;
use App\Models\Donation;
use App\Models\Campaign;
use App\Models\Family;
use App\Models\Orphan;
use App\Models\Sponsorship;

class NotchPayService
{
    /**
     * Initialize a campaign donation payment.
     *
     * @param Donation $donation
     * @param Campaign|null $campaign
     * @return array
     * @throws \Exception
     */
    public function initializeCampaignDonation(Donation $donation, ?Campaign $campaign = null)
    {
        $payload = [
            'amount' => $donation->amount,
            'email' => $donation->donor_email ?? 'customer@example.com',
            'currency' => 'XAF',
            'reference' => $donation->transaction_id,
            'callback' => route('api.donations.callback'),
            'description' => 'Donation for Campaign: ' . ($campaign?->title ?? 'General Fund'),
        ];

        if ($donation->donor_name) {
            $payload['name'] = $donation->donor_name;
        }

        if ($donation->donor_phone) {
            $payload['phone'] = $donation->donor_phone;
        }

        return $this->processPaymentInitialization($payload, $donation);
    }

    /**
     * Initialize a family sponsorship payment.
     *
     * @param Donation $donation
     * @param Family $family
     * @param Sponsorship $sponsorship
     * @return array
     * @throws \Exception
     */
    public function initializeFamilySponsorship(Donation $donation, Family $family, Sponsorship $sponsorship)
    {
        $payload = [
            'amount' => $donation->amount,
            'email' => $donation->donor_email ?? 'customer@example.com',
            'currency' => 'XAF',
            'reference' => $donation->transaction_id,
            'callback' => route('api.sponsorships.callback'),
            'description' => "Family Sponsorship: {$family->widow_name} ({$family->family_code}) - {$sponsorship->payment_frequency}",
        ];

        if ($donation->donor_name) {
            $payload['name'] = $donation->donor_name;
        }

        if ($donation->donor_phone) {
            $payload['phone'] = $donation->donor_phone;
        }

        return $this->processPaymentInitialization($payload, $donation);
    }

    /**
     * Initialize an orphan sponsorship payment.
     *
     * @param Donation $donation
     * @param Orphan $orphan
     * @param Sponsorship $sponsorship
     * @return array
     * @throws \Exception
     */
    public function initializeOrphanSponsorship(Donation $donation, Orphan $orphan, Sponsorship $sponsorship)
    {
        $payload = [
            'amount' => $donation->amount,
            'email' => $donation->donor_email ?? 'customer@example.com',
            'currency' => 'XAF',
            'reference' => $donation->transaction_id,
            'callback' => route('api.sponsorships.callback'),
            'description' => "Orphan Sponsorship: {$orphan->full_name} - {$sponsorship->payment_frequency}",
        ];

        if ($donation->donor_name) {
            $payload['name'] = $donation->donor_name;
        }

        if ($donation->donor_phone) {
            $payload['phone'] = $donation->donor_phone;
        }

        return $this->processPaymentInitialization($payload, $donation);
    }

    /**
     * Process payment initialization with Notch Pay.
     *
     * @param array $payload
     * @param Donation $donation
     * @return array
     * @throws \Exception
     */
    protected function processPaymentInitialization(array $payload, Donation $donation)
    {
        $response = NotchPay::payment()->initialize($payload);

        if ($response->status === 'accepted' || $response->status === 'created') {
            return [
                'success' => true,
                'authorization_url' => $response->authorization_url,
                'reference' => $donation->transaction_id
            ];
        }

        throw new \Exception($response->message ?? 'Failed to initialize payment');
    }

    /**
     * Verify a transaction reference.
     * 
     * @param string $reference
     * @return mixed
     */
    public function verifyPayment(string $reference)
    {
        return NotchPay::payment()->verify($reference);
    }

    /**
     * Process a webhook event payload.
     *
     * @param string $event
     * @param array $data
     * @return bool
     */
    public function processWebhook(string $event, array $data)
    {
        if (!isset($data['reference'])) {
            return false;
        }

        $reference = $data['reference'];
        $donation = Donation::where('transaction_id', $reference)->first();

        if (!$donation) {
            return false;
        }

        if ($event === 'payment.complete') {
            return $this->handleSuccessfulPayment($donation, $data);
        } elseif ($event === 'payment.failed') {
            $donation->update(['status' => 'failed']);
            return true;
        }

        return false;
    }

    /**
     * Handle successful payment based on payment type.
     *
     * @param Donation $donation
     * @param array $data
     * @return bool
     */
    protected function handleSuccessfulPayment(Donation $donation, array $data)
    {
        if ($donation->status === 'completed') {
            return true; // Already processed
        }

        $donation->update([
            'status' => 'completed',
            'payment_date' => now(),
            'payment_method' => $data['method'] ?? $donation->payment_method,
        ]);

        // Handle based on payment type
        switch ($donation->payment_type) {
            case 'campaign_donation':
                $this->updateCampaignAmount($donation);
                break;
            
            case 'family_sponsorship':
                $this->activateFamilySponsorship($donation);
                break;
            
            case 'orphan_sponsorship':
                $this->activateOrphanSponsorship($donation);
                break;
        }

        return true;
    }

    /**
     * Update campaign amount after successful donation.
     *
     * @param Donation $donation
     */
    protected function updateCampaignAmount(Donation $donation)
    {
        if ($donation->campaign_id) {
            $campaign = Campaign::find($donation->campaign_id);
            if ($campaign) {
                $campaign->current_amount += $donation->amount;
                $campaign->save();
            }
        }
    }

    /**
     * Activate family sponsorship after successful payment.
     *
     * @param Donation $donation
     */
    protected function activateFamilySponsorship(Donation $donation)
    {
        $family = $donation->payable;
        
        if ($family && $family instanceof Family) {
            // Find the associated sponsorship
            $sponsorship = Sponsorship::where('family_id', $family->id)
                ->where('user_id', $donation->user_id)
                ->where('status', 'pending')
                ->first();
            
            if ($sponsorship) {
                $sponsorship->update(['status' => 'active']);
            }

            // Update family's total received
            $family->total_received += $donation->amount;
            $family->save();
        }
    }

    /**
     * Activate orphan sponsorship after successful payment.
     *
     * @param Donation $donation
     */
    protected function activateOrphanSponsorship(Donation $donation)
    {
        $orphan = $donation->payable;
        
        if ($orphan && $orphan instanceof Orphan) {
            // Find the associated sponsorship
            $sponsorship = Sponsorship::where('orphan_id', $orphan->id)
                ->where('user_id', $donation->user_id)
                ->where('status', 'pending')
                ->first();
            
            if ($sponsorship) {
                $sponsorship->update(['status' => 'active']);
            }

            // Mark orphan as sponsored
            $orphan->is_sponsored = true;
            $orphan->save();
        }
    }
}
