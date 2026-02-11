class User extends Master {
    constructor() {
        super(); 
        // ANY applicable constructor logic.
    }

    initializeUser() { 
        // ...
    }

    // ...
}

document.addEventListener('DOMContentLoaded', (event) => {
    const user_instance = new User();
    user_instance.initializeUser();
});