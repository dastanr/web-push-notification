var isPushEnabled = false;

window.addEventListener('load', function() {
    var pushButton = document.querySelector('.js-push-button');
    pushButton.addEventListener('click', function() {
        if (isPushEnabled) {
            unsubscribe();
        } else {
            subscribe();
        }
    });

    // Check that service workers are supported, if so, progressively
    // enhance and add push messaging support, otherwise continue without it.
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/serviceworker.js')
            .then(initialiseState);
    } else {
        console.warn('Service workers aren\'t supported in this browser.');
    }

    // Once the service worker is registered set the initial state
    function initialiseState() {
        // Are Notifications supported in the service worker?
        if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
            console.warn('Notifications aren\'t supported.');
            return;
        }

        // Check the current Notification permission.
        // If its denied, it's a permanent block until the
        // user changes the permission
        if (Notification.permission === 'denied') {
            console.warn('The user has blocked notifications.');
            return;
        }

        // Check if push messaging is supported
        if (!('PushManager' in window)) {
            console.warn('Push messaging isn\'t supported.');
            return;
        }

        // We need the service worker registration to check for a subscription
        navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
            // Do we already have a push message subscription?
            serviceWorkerRegistration.pushManager.getSubscription()
                .then(function(subscription) {
                    // Enable any UI which subscribes / unsubscribes from
                    // push messages.
                    var pushButton = document.querySelector('.js-push-button');
                    pushButton.disabled = false;

                    if (!subscription) {
                        // We aren't subscribed to push, so set UI
                        // to allow the user to enable push
                        return;
                    }

                    // Keep our server in sync with the latest subscriptionId
                    var subscriptionId = getGcmRegistrationId(subscription);

                    if (getCookie('token') == null) {
                        return sendSubscriptionToServer(subscription);
                    } else {

                        var UserToken = getCookie('token').split(',');
                        console.log(UserToken);
                        if (UserToken[0] == subscriptionId[0]) {
                            console.log('User have current Token');
                            // Set your UI to show they have subscribed for
                            // push messages
                            pushButton.textContent = 'Disable Push Messages';
                            isPushEnabled = true;
                            return sendSubscriptionToServer(subscription);
                        } else {
                            console.log('removing the old token');
                            deleteOldToken(UserToken[0]);
                            console.log('Susbscribing the new token');
                            sendSubscriptionToServer(subscription);
                        }

                    }

                    // Set your UI to show they have subscribed for
                    // push messages
                    pushButton.textContent = 'Disable Push Messages';
                    isPushEnabled = true;

                })
                .catch(function(err) {
                    console.warn('Error during getSubscription()', err);
                });
        });
    }


    function subscribe() {
        // Disable the button so it can't be changed while
        // we process the permission request
        var pushButton = document.querySelector('.js-push-button');
        pushButton.disabled = true;

        navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
            serviceWorkerRegistration.pushManager.subscribe({
                    userVisibleOnly: true
                })
                .then(function(subscription) {
                    // The subscription was successful
                    isPushEnabled = true;
                    pushButton.textContent = 'Disable Push Messages';
                    pushButton.disabled = false;

                    var subscriptionId = getGcmRegistrationId(subscription);
                    if (getCookie('token') == null) {
                        return sendSubscriptionToServer(subscription);
                    } else {

                        var UserToken = getCookie('token').split(',');
                        console.log(UserToken);
                        if (UserToken[0] == subscriptionId[0]) {
                            console.log('User have current Token');
                            return sendSubscriptionToServer(subscription);
                        } else {
                            console.log('removing the old token');
                            deleteOldToken(UserToken[0]);
                            console.log('Susbscribing the new token');
                            sendSubscriptionToServer(subscription);
                        }

                    }
                })
                .catch(function(e) {
                    if (Notification.permission === 'denied') {
                        // The user denied the notification permission which
                        // means we failed to subscribe and the user will need
                        // to manually change the notification permission to
                        // subscribe to push messages
                        console.warn('Permission for Notifications was denied');
                        alert('Notifications are blocked by you, Please manually change the notification permission.');
                        pushButton.disabled = true;
                    } else {
                        // A problem occurred with the subscription; common reasons
                        // include network errors, and lacking gcm_sender_id and/or
                        // gcm_user_visible_only in the manifest.
                        console.error('Unable to subscribe to push.', e);
                        pushButton.disabled = false;
                        pushButton.textContent = 'Enable Push Messages';
                    }
                });
        });
    }

    function unsubscribe() {
        var pushButton = document.querySelector('.js-push-button');
        pushButton.disabled = true;

        navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
            // To unsubscribe from push messaging, you need get the
            // subscription object, which you can call unsubscribe() on.
            serviceWorkerRegistration.pushManager.getSubscription().then(
                function(pushSubscription) {
                    // Check we have a subscription to unsubscribe
                    if (!pushSubscription) {
                        // No subscription object, so set the state
                        // to allow the user to subscribe to push
                        isPushEnabled = false;
                        pushButton.disabled = false;
                        pushButton.textContent = 'Enable Push Messages';
                        return;
                    }
                    var subscriptionId = pushSubscription.subscriptionId;

                    //send the push ID to Server to unsubscribe
                    sendUnSubscriptionToServer(pushSubscription);

                    // We have a subscription, so call unsubscribe on it
                    pushSubscription.unsubscribe().then(function(successful) {
                        pushButton.disabled = false;
                        pushButton.textContent = 'Enable Push Messages';
                        isPushEnabled = false;
                    }).catch(function(e) {
                        // We failed to unsubscribe, this can lead to
                        // an unusual state, so may be best to remove
                        // the users data from your data store and
                        // inform the user that you have done so

                        console.log('Unsubscription error: ', e);
                        pushButton.disabled = false;
                        pushButton.textContent = 'Enable Push Messages';
                    });
                }).catch(function(e) {
                console.error('Error thrown while unsubscribing from push messaging.', e);
            });
        });
    }

    function sendSubscriptionToServer(sub) {
        const subscriptionObject = JSON.parse(JSON.stringify(sub));
        var key = subscriptionObject.keys.p256dh;
        var token = subscriptionObject.keys.auth;
        console.log('endpoint:', sub.endpoint);
        var subscriptionId = getGcmRegistrationId(sub);
        console.log(subscriptionId);

        $.ajax({
            type: "POST",
            url: "/web_push/subscribers/add",
            dataType: "json",
            //contentType: "application/json; charset=utf-8",
            data: {
                register: "1",
                subscriber: subscriptionId[0],
                browser: subscriptionId[1],
                crpt_key: key,
                auth: token
            },
            success: function(data) {
                //console.log(data);
                //Set cookie to accept and expire date to 7 days
                var d = new Date();
                d.setTime(d.getTime() + (7 * 24 * 60 * 60 * 1000));
                var expires = "expires=" + d.toUTCString();
                document.cookie = "token=" + subscriptionId + "; notification_cookie=1;" + expires + "; path=/";

            }
        });
    }


    function sendUnSubscriptionToServer(sub) {
        const subscriptionObject = JSON.parse(JSON.stringify(sub));
        var key = subscriptionObject.keys.p256dh;
        var token = subscriptionObject.keys.auth;
        console.log('endpoint:', sub.endpoint);
        var subscriptionId = getGcmRegistrationId(sub);
        console.log(subscriptionId);

        $.ajax({
            type: "POST",
            url: "/web_push/subscribers/unsubscribe",
            dataType: "json",
            //contentType: "application/json; charset=utf-8",
            data: {
                register: "0",
                subscriber: subscriptionId[0],
                browser: subscriptionId[1],
                crpt_key: key,
                auth: token
            },
            success: function(data) {
                console.log('success');
            }
        });
    }



    function deleteOldToken(sub) {
        $.ajax({
            type: "POST",
            url: "/web_push/subscribers/unsubscribe",
            dataType: "json",
            //contentType: "application/json; charset=utf-8",
            data: {
                subscriber: sub,
            },
            success: function(data) {
                console.log('success');
            }
        });
    }



    function getGcmRegistrationId(sub) {
        var output = new Array(2);
        if (sub.subscriptionId) {
            output[0] = sub.subscriptionId;
            output[1] = "chrome"
            return output;
        }

        var endpoint = 'https://android.googleapis.com/gcm/send/';
        var parts = sub.endpoint.split(endpoint);
        if (parts.length > 1) {
            output[0] = parts[1];
            output[1] = "chrome"
            return output;
        } else {
            var endpoint = 'https://updates.push.services.mozilla.com/wpush/v1/';
            var parts = sub.endpoint.split(endpoint);
            if (parts.length > 1) {
                output[0] = parts[1];
                output[1] = "firefox"
                return output;
            }
        }
    }

    //Get cookie by name
    function getCookie(name) {
        var nameEQ = name + "=";
        //alert(document.cookie);
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ')
                c = c.substring(1);
            if (c.indexOf(nameEQ) != -1)
                return c.substring(nameEQ.length, c.length);
        }
        return null;
    }


});
