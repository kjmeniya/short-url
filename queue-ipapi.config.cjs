module.exports = {
    apps: [
        {
            name: "queue-ipapi",
            script: "/usr/bin/php", // <-- full path to php
            args: [
                "/var/www/softdev.in/artisan",  // <-- full path to artisan
                "queue:work",
                "--queue=emails",
                "--sleep=3",
                "--tries=3",
                "--timeout=90"
            ],
            interpreter: null,
            autorestart: true
        }
    ]
};