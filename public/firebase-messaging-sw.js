// Import Firebase scripts
importScripts(
    "https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"
);
importScripts(
    "https://www.gstatic.com/firebasejs/9.22.1/firebase-messaging-compat.js"
);

// Initialize Firebase inside the Service Worker
firebase.initializeApp({
    apiKey: "AIzaSyAFX-YIhZ9Ny235KkGEXerE-njXngzZ9uU",
    authDomain: "ocean-dbc54.firebaseapp.com",
    projectId: "ocean-dbc54",
    messagingSenderId: "85798723141",
    appId: "1:85798723141:web:818447123fb6e7fbbf0d2c",
});

// Retrieve an instance of Firebase Messaging
const messaging = firebase.messaging();

// Listen for background messages
messaging.onBackgroundMessage(function (payload) {
    self.registration.showNotification(payload.notification.title, {
        body: payload.notification.body,
        icon: payload.notification.icon || "/favicon.ico",
    });
});
