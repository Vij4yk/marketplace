<?php

Auth::routes();

Route::get('register/confirm/{token}', [
	'uses' => 'Account\EmailController@confirmEmail',
	'as'   => 'verify.email',
]);

Route::post('/verification', [
	'uses' => 'Account\EmailController@confirmEmailAgain',
	'as'   => 'send.verification.code',
]);

Route::get('/', 'HomeController@index')->name('home');

Route::get('/about', 'HomeController@about')->name('about');

Route::get('/account/connect', 'Account\MarketPlaceConnectController@index')->name('account.connect');
Route::get('/account/connect/complete', 'Account\MarketPlaceConnectController@store')->name('account.complete');

Route::group(['prefix' => '/account', 'middleware' => ['auth'], 'namespace' => 'Account'], function() {
	Route::get('/', 'AccountController@index')->name('account');
	Route::get('/bought/files', 'AccountController@boughtIndex')->name('bought.files');
	Route::get('/files/sold', 'AccountController@filesSold')->name('files.sold');
	Route::post('/upload/avatar', 'AvatarController@store')->name('account.user.avatar');
	Route::post('/update/settings', 'AccountController@update')->name('account.update.settings');
	Route::get('/unread-notifications', 'AccountController@getUnreadNotifications')->name('get.unread.notifications');
	Route::get('/all-notifications', 'AccountController@getAllNotifications')->name('get.all.notifications');
	Route::get('/notification/{id}', 'AccountController@showNotification')->name('show.notification');
	Route::get('/notifications/mark/{id}/read', 'AccountController@markAsRead')->name('notification.mark.as.read');
	Route::get('/change/password/', 'AccountController@changePassword')->name('change.password');
	Route::post('/change/password', 'AccountController@changePasswordStore')->name('change.password.store');

	Route::group(['prefix' => '/files', 'middleware' => ['needs.marketplace']], function() {
		Route::get('/', 'FileController@index')->name('account.files.index');
		Route::get('/{file}/edit', 'FileController@edit')->name('account.files.edit');
		Route::patch('/{file}', 'FileController@update')->name('account.files.update');
		Route::post('/{file}', 'FileController@store')->name('account.files.store');
		Route::get('/create', 'FileController@create')->name('account.files.create.start');
		Route::get('/{file}/create', 'FileController@create')->name('account.files.create');

		Route::get('/{file}/create/notification', 'DatabaseNotificationsController@index')->name('account.files.create.notification');
		Route::post('/{file}/store/notification', 'DatabaseNotificationsController@notifyOfChanges')->name('account.files.store.notification');
	});
});

Route::group(['prefix' => '/admin', 'namespace' => 'Admin', 'middleware' => ['auth', 'admin']], function() {
	Route::get('/', 'AdminController@index')->name('admin.index');
	Route::get('/preview/{file}', 'PreviewFileController@show')->name('admin.files.show');

	Route::get('/categories', 'CategoriesController@index')->name('admin.categories');
	Route::get('/categories/create', 'CategoriesController@create')->name('admin.category.create');
	Route::get('/categories/edit/{slug}', 'CategoriesController@edit')->name('admin.category.edit');
	Route::post('/categories/store', 'CategoriesController@store')->name('admin.category.store');
	Route::post('/categories/update/{id}', 'CategoriesController@update')->name('admin.category.update');

	Route::group(['prefix' => '/files'], function() {
		Route::group(['prefix' => '/new'], function() {
			Route::get('/', 'FileNewController@index')->name('admin.files.new.index');
			Route::patch('/{file}', 'FileNewController@update')->name('admin.files.new.update');
			Route::get('/{file}/rejection', 'FileNewController@newFileRejectionNotification')->name('admin.file.rejection');
			Route::post('/{file}', 'FileNewController@destroy')->name('admin.files.new.destroy');
		});

		Route::group(['prefix' => '/updated'], function() {
			Route::get('/', 'FileUpdatedController@index')->name('admin.files.updated.index');
			Route::patch('/{file}', 'FileUpdatedController@update')->name('admin.files.updated.update');
			Route::get('/{file}/rejection', 'FileUpdatedController@updatedFileRejectionNotification')->name('admin.updated.file.rejection');
			Route::post('/{file}', 'FileUpdatedController@destroy')->name('admin.files.updated.destroy');
		});
	});

	Route::get('/users/all', 'AdminController@users')->name('admin.users.all');
	Route::post('impersonate/{id}', 'AdminController@impersonate')->name('admin.impersonate');
});

Route::delete('impersonate/delete', 'Admin\AdminController@destroyImpersonate')->name('admin.impersonate.delete');

Route::group(['prefix' => '/{file}/checkout', 'namespace' => 'Checkout'], function() {
	Route::post('/free', 'CheckoutController@free')->name('checkout.free');
	Route::post('/payment', 'CheckoutController@payment')->name('checkout.payment');
});

Route::post('/{file}/upload', 'Upload\UploadController@store')->name('upload.store');
Route::delete('/{file}/upload/{upload}', 'Upload\UploadController@destroy')->name('upload.destroy');

Route::post('/{file}/preview/upload', 'Upload\PreviewGalleryController@store')->name('upload.preview.store');
Route::delete('/{file}/destroy/{upload}', 'Upload\PreviewGalleryController@destroy')->name('preview.destroy');

Route::get('/files', 'Files\FileController@index')->name('files.index');
Route::get('/files/category/{slug}', 'Files\FileController@getFilesByCategory')->name('file.categories');
Route::get('/{file}', 'Files\FileController@show')->name('files.show');
Route::get('/{file}/{sale}/download', 'Files\FileDownloadController@show')->name('files.download');
Route::get('/{file}/download', 'Files\FileDownloadController@adminDownload')->name('files.admin.download');

Route::group(['middleware' => ['auth']], function() {
	Route::post('/store/comment/{id}', 'Files\FileController@storeComment')->name('store.comment');
	Route::post('/store/comment/reply/{file}/{id}', 'Files\FileController@storeReply')->name('store.comment.reply');
	Route::get('/notifications/mark/all/read', 'Account\DatabaseNotificationsController@markAllAsRead')->name('notifications.mark.all.as.read');

	Route::group(['middleware' => ['auth', 'admin']], function() {
		Route::delete('/destroy/comment/{id}/{fileId}', 'Files\FileController@destroyComment')->name('destroy.comment');
	});
});