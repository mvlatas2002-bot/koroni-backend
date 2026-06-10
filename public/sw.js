self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
  let data = {};

  try {
    data = event.data ? event.data.json() : {};
  } catch {
    data = {};
  }

  const title = data.title || 'Koroni Portal';
  const options = {
    body: data.body || 'Υπάρχει νέα ειδοποίηση στο portal.',
    badge: '/favicon.ico',
    icon: '/favicon.ico',
    data: {
      url: data.url || '/notifications',
      notificationId: data.notificationId || null,
    },
    tag: data.notificationId || undefined,
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const targetUrl = new URL(event.notification.data?.url || '/notifications', self.location.origin);

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
      for (const client of clients) {
        if ('focus' in client) {
          client.navigate(targetUrl.href);
          return client.focus();
        }
      }

      return self.clients.openWindow(targetUrl.href);
    })
  );
});
