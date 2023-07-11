/*self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) { return; }
    var sendNotification = function(message,title,icon,badge,url,actionName,actionText,bstMatchUrl) {
        return self.registration.showNotification(title, {
                body: message,
                actions: [
                            {action: actionName, title: actionText},
                            {action: 'view_transactions', title: '💼 Transactions'},
                        ],
                icon: icon,
                badge: badge,
                silent: false,
                data: { url:url,bestMatchUrl:bstMatchUrl},
                vibrate: [100, 50, 100]
            });
        };
    event.waitUntil(
        self.registration.pushManager.getSubscription().then(function(subscription) {
            if (!subscription) { return; }
            return fetch('https://gintonic.instaswap.io/AJAXPOSTS?action=pushNotifMessage&endpoint=' + encodeURIComponent(subscription.endpoint)).then(function (response) {
                if (response.status !== 200) { throw new Error(); }
                return response.json().then(function (data) {
                    if (data.error || !data.notification) { throw new Error(); }
                    return sendNotification(data.notification.message,data.notification.title,data.notification.icon,data.notification.badge,data.notification.url,data.notification.actionText,data.notification.actionName,data.notification.bstMatchUrl);
                });
            }).catch(function () {
                return sendNotification();
            });
        })
    );
});*/

/*self.addEventListener('notificationclick', function(event) {

        switch(event.action){
            case 'view_transactions':
                clients.openWindow(event.notification.data.url);
                break;
                case 'best_match':
                clients.openWindow(event.notification.data.bestMatchUrl);
                break;
        }
    }
    , false);*/

