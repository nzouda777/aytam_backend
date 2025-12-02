<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\OrphanController;
use App\Http\Controllers\Api\SponsorshipController;
use App\Http\Controllers\Api\DisbursementController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Campagnes publiques
Route::prefix('campaigns')->group(function () {
    Route::get('/', [CampaignController::class, 'index']);
    Route::get('/featured', [CampaignController::class, 'featured']);
    Route::get('/active', [CampaignController::class, 'active']);
    Route::get('/urgent', [CampaignController::class, 'urgent']);
    Route::get('/{id}', [CampaignController::class, 'show']);
});

// Catégories publiques
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{id}', [CategoryController::class, 'show']);
Route::get('categories/{id}/campaigns', [CategoryController::class, 'campaigns']);

// Routes protégées (nécessite authentification)
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentification
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'statistics']);
        Route::get('/overview', [DashboardController::class, 'overview']);
        Route::get('/monthly-donations', [DashboardController::class, 'monthlyDonations']);
        Route::get('/campaign-categories', [DashboardController::class, 'campaignCategories']);
        Route::get('/recent-campaigns', [DashboardController::class, 'recentCampaigns']);
        Route::get('/recent-donations', [DashboardController::class, 'recentDonations']);
        Route::get('/alerts', [DashboardController::class, 'alerts']);
        Route::get('/donor-stats', [DashboardController::class, 'donorStats']);
    });

    // Dons
    Route::prefix('donations')->group(function () {
        Route::get('/', [DonationController::class, 'index']);
        Route::post('/', [DonationController::class, 'store']);
        Route::get('/statistics', [DonationController::class, 'statistics']);
        Route::get('/my-donations', [DonationController::class, 'userDonations']);
        Route::get('/campaign/{campaignId}', [DonationController::class, 'campaignDonations']);
        Route::get('/{id}', [DonationController::class, 'show']);
        Route::patch('/{id}/status', [DonationController::class, 'updateStatus']);
    });

    // Parrainages
    Route::prefix('sponsorships')->group(function () {
        Route::get('/', [SponsorshipController::class, 'index']);
        Route::post('/', [SponsorshipController::class, 'store']);
        Route::get('/active', [SponsorshipController::class, 'active']);
        Route::get('/statistics', [SponsorshipController::class, 'statistics']);
        Route::get('/my-sponsorships', [SponsorshipController::class, 'userSponsorships']);
        Route::get('/family/{familyId}', [SponsorshipController::class, 'familySponsorships']);
        Route::get('/{id}', [SponsorshipController::class, 'show']);
        Route::put('/{id}', [SponsorshipController::class, 'update']);
        Route::delete('/{id}', [SponsorshipController::class, 'destroy']);
        Route::patch('/{id}/status', [SponsorshipController::class, 'updateStatus']);
    });

    // Routes Admin/Manager seulement
    Route::middleware('role:admin,manager')->group(function () {
        
        // Gestion des campagnes
        Route::prefix('campaigns')->group(function () {
            Route::post('/', [CampaignController::class, 'store']);
            Route::put('/{id}', [CampaignController::class, 'update']);
            Route::delete('/{id}', [CampaignController::class, 'destroy']);
        });

        // Gestion des catégories
        Route::prefix('categories')->group(function () {
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{id}', [CategoryController::class, 'update']);
            Route::delete('/{id}', [CategoryController::class, 'destroy']);
        });

        // Familles
        Route::prefix('families')->group(function () {
            Route::get('/', [FamilyController::class, 'index']);
            Route::post('/', [FamilyController::class, 'store']);
            Route::get('/statistics', [FamilyController::class, 'statistics']);
            Route::get('/{id}', [FamilyController::class, 'show']);
            Route::put('/{id}', [FamilyController::class, 'update']);
            Route::delete('/{id}', [FamilyController::class, 'destroy']);
            Route::get('/{id}/orphans', [FamilyController::class, 'orphans']);
            Route::patch('/{id}/status', [FamilyController::class, 'updateStatus']);
        });

        // Orphelins
        Route::prefix('orphans')->group(function () {
            Route::get('/', [OrphanController::class, 'index']);
            Route::post('/', [OrphanController::class, 'store']);
            Route::get('/sponsored', [OrphanController::class, 'sponsored']);
            Route::get('/unsponsored', [OrphanController::class, 'unsponsored']);
            Route::get('/statistics', [OrphanController::class, 'statistics']);
            Route::get('/{id}', [OrphanController::class, 'show']);
            Route::put('/{id}', [OrphanController::class, 'update']);
            Route::delete('/{id}', [OrphanController::class, 'destroy']);
        });

        // Décaissements
        Route::prefix('disbursements')->group(function () {
            Route::get('/', [DisbursementController::class, 'index']);
            Route::post('/', [DisbursementController::class, 'store']);
            Route::get('/this-month', [DisbursementController::class, 'thisMonth']);
            Route::get('/this-year', [DisbursementController::class, 'thisYear']);
            Route::get('/statistics', [DisbursementController::class, 'statistics']);
            Route::get('/family/{familyId}', [DisbursementController::class, 'familyDisbursements']);
            Route::get('/campaign/{campaignId}', [DisbursementController::class, 'campaignDisbursements']);
            Route::get('/{id}', [DisbursementController::class, 'show']);
            Route::put('/{id}', [DisbursementController::class, 'update']);
            Route::delete('/{id}', [DisbursementController::class, 'destroy']);
        });
    });
});

// Route de fallback
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route non trouvée',
    ], 404);
});