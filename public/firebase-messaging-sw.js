// Import Firebase scripts
importScripts(
    "https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"
);
importScripts(
    "https://www.gstatic.com/firebasejs/9.22.1/firebase-messaging-compat.js"
);

// Initialize Firebase inside the Service Worker
firebase.initializeApp({
    apiKey: "AIzaSyBnr2SRw95NoCLK92g-Yu0XaOdeUhy0xbk",
    authDomain: "vrundavan-project.firebaseapp.com",
    projectId: "vrundavan-project",
    messagingSenderId: "311834504462",
  appId: "1:311834504462:web:e301b002bdeb10bf007b5f",
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
