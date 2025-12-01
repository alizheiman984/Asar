<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OTPController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\PointController;
use App\Http\Controllers\Api\SupplyController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\FinancialController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\VolunteerController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\BenefactorController;
use App\Http\Controllers\Api\GovernmentController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CampaignTypeController;
use App\Http\Controllers\Api\DonorPaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\VolunteerTeamController;
use App\Http\Controllers\Api\GovernmentAuthController;
use App\Http\Controllers\Api\SpecializationController;
use App\Http\Controllers\Api\BusinessInformationController;

// Public routes
Route::post('/volunteer/register', [AuthController::class, 'volunteerRegister']);
Route::post('/volunteer/login', [AuthController::class, 'volunteerLogin']);

Route::post('/team/register', [AuthController::class, 'teamRegister']);
Route::post('/team/login', [AuthController::class, 'teamLogin']);
Route::post('/send-otp', [OTPController::class, 'sendOTP']);
Route::post('/verify-otp', [OTPController::class, 'verifyOTP']);
Route::post('/update/Password', [OTPController::class, 'updatePasswordForVolunteerEntities']);

Route::post('employee/login', [VolunteerTeamController::class, 'LoginEmployee']);

 // Campaign Types routes
 Route::apiResource('campaign-types', CampaignTypeController::class);

 // Specializations routes
 Route::apiResource('specializations', SpecializationController::class);
 
// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    //volunteer
    Route::get('/volunteer/profile', [AuthController::class, 'profileVolunteer']);
    Route::post('/volunteer/profile/update', [AuthController::class, 'updateProfilevolunteer']);

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

   

    // Volunteer Teams routes
    Route::apiResource('volunteer-teams', VolunteerTeamController::class);
    Route::apiResource('business-information', BusinessInformationController::class);

    // Volunteers routes
    Route::apiResource('volunteers', VolunteerController::class);

    // Employees routes
    Route::apiResource('employees', EmployeeController::class);

    // Campaigns routes
    Route::apiResource('campaigns', CampaignController::class);
    Route::get('get/campaigns/By/Specialty', [CampaignController::class,'getcampaignsBySpecialty']);
    Route::post('campaigns/{campaign}/volunteers', [CampaignController::class, 'addVolunteer']);
    Route::delete('campaigns/{id}/volunteers', [CampaignController::class, 'removeVolunteer']);


    // notifications routes
    Route::get('/notifications', [NotificationController::class, 'index']);

    // Points routes
    Route::apiResource('points', PointController::class);

    // Requests routes
    Route::apiResource('requests', RequestController::class);
    Route::get('get-requests-team', [RequestController::class,'indexForEmployee']);

    
    // Attendances routes
    Route::apiResource('attendances', AttendanceController::class);

    Route::post('/attendances/{id}', [AttendanceController::class, 'update']);
    Route::get('/attendances/campaign/{id}', [AttendanceController::class, 'getAttendancesByCampaign']);

    ///Certificate
    Route::get('generateCertificate',[CertificateController ::class,'generateCertificate']);

    // Benefactors routes
    Route::apiResource('benefactors', BenefactorController::class);

    // Donor Payments routes
    Route::apiResource('donor-payments', DonorPaymentController::class);

  


    Route::post('stripe/webhook', [DonorPaymentController::class, 'handleWebhook']);



    // Financials routes
    Route::apiResource('financials', FinancialController::class);

    Route::get('getDonations/{id}',[FinancialController::class,'getDonations']);
    Route::post('update/StutasDonations/{id}',[FinancialController::class,'updatestutas']);


    
    // Chats routes
    Route::apiResource('chats', ChatController::class);

    Route::post('send-message/{chat_room}', [ChatController::class,'sendMessage']);

    Route::get('get-Messages/{chat_room}', [ChatController::class,'getMessages']);

    Route::get('my-chat-rooms', [ChatController::class,'myChatRooms']);


    // inventory
    Route::get('/inventory', [InventoryController::class, 'index']);

    // items
    
    Route::get('/items', [ItemController::class, 'index']);
    Route::post('/items', [ItemController::class, 'store']);
    Route::get('/items/{id}', [ItemController::class, 'show']);
    Route::put('/items/{id}', [ItemController::class, 'update']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);


    // Supplies
    Route::post('/supplies/add', [SupplyController::class, 'addSupply']);
    Route::post('/supplies/consume', [SupplyController::class, 'consume']);

    Route::get('/team/supplies', [SupplyController::class, 'teamSupplies']);






    // Contracts routes
    Route::apiResource('contracts', ContractController::class);


    Route::get('/get/all/volunteer/teams', [DonorPaymentController::class, 'getallteamaccepted']);

    Route::post('store/employee',[VolunteerTeamController::class,'storeEmployee']);
    Route::get('employee/profile',[EmployeeController::class,'profileEmployee']);
    Route::put('employee/update',[EmployeeController::class,'updateEmployee']);
    Route::get('get-employee-campaigns-pending',[EmployeeController::class,'getEmployeeCampaignsPending']);
    Route::get('get-employee-campaigns-done',[EmployeeController::class,'getEmployeeCampaignsDone']);

    Route::post('campaigns/create', [CampaignController::class, 'storeCampaign']);
});

// Government Routes
Route::middleware(['auth:sanctum', 'government.only'])->group(function () {
    Route::get('/government/teams', [GovernmentController::class, 'getTeams']);
    Route::get('/government/charities', [GovernmentController::class, 'getCharities']);
    Route::get('/government/teams/pending', [GovernmentController::class, 'getPendingTeams']);
    Route::post('/government/teams/{team}/approve', [GovernmentController::class, 'approveTeam']);
    Route::post('/government/teams/{team}/reject', [GovernmentController::class, 'rejectTeam']);
    Route::get('/government/teams/{team}', [GovernmentController::class, 'getTeamDetails']);
    Route::get('/government/teams/{team}/total-finance', [GovernmentController::class, 'getListTeamFinance']);
    Route::get('/government/teams/{team}/list-finance', [GovernmentController::class, 'getTotalTeamFinance']);
    Route::get('/government/teams/{team}/campaigns', [GovernmentController::class, 'getTeamCampaigns']);
    Route::get('/government/teams/{team}/employees', [GovernmentController::class, 'getTeamEmployees']);
    Route::get('/government/volunteers', [GovernmentController::class, 'getAllVolunteers']);
});

// Government Authentication Routes
Route::prefix('government')->group(function () {
    Route::post('/register', [GovernmentAuthController::class, 'register']);
    Route::post('/login', [GovernmentAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [GovernmentAuthController::class, 'logout']);
    });
});

// Volunteer Team Routes
Route::middleware(['auth:sanctum', 'team.only'])->group(function () {
    Route::prefix('teams')->group(function () {
        // Statistics
        Route::get('/statistics', [VolunteerTeamController::class, 'getTeamStatistics']);
        
        // Campaigns
        Route::get('/campaigns', [VolunteerTeamController::class, 'getTeamCampaigns']);
        
        // Employees
        Route::get('/employees', [VolunteerTeamController::class, 'getMyEmployees']);
        
        // Contracts
        Route::get('/contracts', [VolunteerTeamController::class, 'getTeamContracts']);
        Route::post('/contracts', [VolunteerTeamController::class, 'storeContract']);
        Route::get('/contracts/{contract}', [VolunteerTeamController::class, 'showContract']);
        Route::put('/contracts/{contract}', [VolunteerTeamController::class, 'updateContract']);
        Route::delete('/contracts/{contract}', [VolunteerTeamController::class, 'deleteContract']);
    });
});
