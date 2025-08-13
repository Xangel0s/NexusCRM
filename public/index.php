<?php
// Nexus front controller
declare(strict_types=1);
// Autoloader
spl_autoload_register(function($class){
  $base = dirname(__DIR__);
  $class = str_replace('\\','/',$class);
  $rel = $class;
  if (str_starts_with($rel, 'App/')) { $rel = substr($rel, 4); }
  $paths = ["$base/app/$rel.php", "$base/$rel.php", "$base/app/$class.php", "$base/$class.php"];
  foreach($paths as $p){ if(file_exists($p)){ require_once $p; return; } }
});
require_once dirname(__DIR__).'/app/bootstrap.php';
use App\Router; $r=new Router();
$r->get('/login',[App\Controllers\AuthController::class,'loginForm']);
$r->post('/login',[App\Controllers\AuthController::class,'login']);
$r->post('/logout',[App\Controllers\AuthController::class,'logout']);
$r->get('/',[App\Controllers\HomeController::class,'index']);
$r->get('/home',[App\Controllers\HomeController::class,'index']);

// Admin: users
$r->get('/users',[App\Controllers\UserController::class,'index']);
$r->get('/users-create',[App\Controllers\UserController::class,'create']);
$r->post('/users-store',[App\Controllers\UserController::class,'store']);
$r->get('/users-edit',[App\Controllers\UserController::class,'edit']);
$r->post('/users-update',[App\Controllers\UserController::class,'update']);
$r->post('/users-toggle',[App\Controllers\UserController::class,'toggle']);

// Backdata
$r->get('/backdata/summary',[App\Controllers\BackdataController::class,'summary']);
$r->get('/backdata/leads',[App\Controllers\BackdataController::class,'leads']);
$r->get('/backdata/leads/day-preview',[App\Controllers\BackdataController::class,'leadsDayPreview']);
$r->get('/backdata/leads/export',[App\Controllers\BackdataController::class,'leadsExport']);
$r->get('/backdata/sellers',[App\Controllers\BackdataController::class,'sellers']);
$r->get('/backdata/seller/preview',[App\Controllers\BackdataController::class,'sellerPreview']);
$r->get('/backdata/bases',[App\Controllers\BackdataController::class,'bases']);
$r->get('/backdata/bases/progreso',[App\Controllers\BackdataController::class,'basesProgress']);
$r->get('/backdata/base',[App\Controllers\BackdataController::class,'baseDetail']);
$r->get('/backdata/base/preview',[App\Controllers\BackdataController::class,'basePreview']);
$r->get('/backdata/base/export',[App\Controllers\BackdataController::class,'baseExport']);
$r->post('/backdata/base/archive',[App\Controllers\BackdataController::class,'baseArchive']);
$r->post('/backdata/base/rename',[App\Controllers\BackdataController::class,'baseRename']);
$r->get('/backdata/assign',[App\Controllers\BackdataController::class,'assignForm']);
$r->post('/backdata/assign',[App\Controllers\BackdataController::class,'assignRun']);
$r->get('/backdata/assign/preview',[App\Controllers\BackdataController::class,'assignPreview']);

// Import CSV (backdata)
$r->get('/backdata/import',[App\Controllers\ImportController::class,'importForm']);
$r->post('/backdata/import/parse',[App\Controllers\ImportController::class,'importParse']);
$r->post('/backdata/import/commit',[App\Controllers\ImportController::class,'importCommit']);

// Seller
$r->get('/seller/my-leads',[App\Controllers\SellerController::class,'myLeads']);
$r->get('/seller/lead',[App\Controllers\SellerController::class,'leadDetail']);
$r->post('/seller/tipify',[App\Controllers\SellerController::class,'tipify']);
$r->post('/seller/release',[App\Controllers\SellerController::class,'release']);

// Announcements
$r->get('/announcements',[App\Controllers\AnnouncementController::class,'index']);
$r->get('/announcements/create',[App\Controllers\AnnouncementController::class,'create']);
$r->post('/announcements/store',[App\Controllers\AnnouncementController::class,'store']);
$r->post('/announcements/delete',[App\Controllers\AnnouncementController::class,'delete']);
$r->dispatch();
