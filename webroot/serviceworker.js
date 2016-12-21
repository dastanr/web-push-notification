/*
*
*  Push Notifications codelab
*  Copyright 2015 Google Inc. All rights reserved.
*
*  Licensed under the Apache License, Version 2.0 (the "License");
*  you may not use this file except in compliance with the License.
*  You may obtain a copy of the License at
*
*      https://www.apache.org/licenses/LICENSE-2.0
*
*  Unless required by applicable law or agreed to in writing, software
*  distributed under the License is distributed on an "AS IS" BASIS,
*  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*  See the License for the specific language governing permissions and
*  limitations under the License
*
*/

// Version 0.1
'use strict';

console.log('Started', self);

self.addEventListener('install', function(event) {
  self.skipWaiting();
  console.log('Installed', event);
});

self.addEventListener('activate', function(event) {
  console.log('Activated', event);
});

self.addEventListener('push', function(event) {
  console.log('Received a push message', event);
  var data = JSON.parse(event.data.text());
  var title = data.title;
  var body = data.body;
  var icon = data.icon;
  var url = data.url;
  event.waitUntil(
    Promise.all([
      self.registration.showNotification(title, {
 		  body: body,
 		  icon: icon,
 		  data: {
             url: data.url
           }
 		})
    ])
  );
  });

self.addEventListener('notificationclick', function(event) {
    console.log('Notification click: url ', event.notification.data.url);
    event.notification.close();
    var url = event.notification.data.url;
	if(url!==undefined){
		// Chek url is already opened then just focus on it else open this url in new window
		event.waitUntil(
			clients.matchAll({
				type: 'window'
			})
			.then(function(windowClients) {
				for (var i = 0; i < windowClients.length; i++) {
					var client = windowClients[i];
					if (client.url === url && 'focus' in client) {
						return client.focus();
					}
				}
				if (clients.openWindow) {
					return clients.openWindow(url);
				}
			})
		);
	}
});
