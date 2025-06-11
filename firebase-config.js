// Configuración de Firebase
const firebaseConfig = {
    apiKey: "AIzaSyA8cKi8j4S0xtPhQne1N22_DQ78mFX_9lE",
    authDomain: "web-page-achiad.firebaseapp.com",
    projectId: "web-page-achiad",
    storageBucket: "web-page-achiad.appspot.com",
    messagingSenderId: "976716067507",
    appId: "1:976716067507:web:da643f2b8fa027904636c6",
    measurementId: "G-V6PBBB8WM9"
};

// Inicializar Firebase
if (!firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
}

// Exportar las instancias
const db = firebase.firestore();
const auth = firebase.auth();
const storage = firebase.storage();

// Hacer las variables disponibles globalmente
window.db = db;
window.auth = auth;
window.storage = storage; 