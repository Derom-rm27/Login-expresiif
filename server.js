const express = require('express');
const cors = require('cors');
const session = require('express-session');
const path = require('path');
const dotenv = require('dotenv');

const app = express();


const PORT = 3000;

// Configuración
dotenv.config();

app.use(cors({
    origin: "http://localhost:3000",
    credentials: true
}));

app.use(express.json());
app.use(express.urlencoded({extended: true}));
app.use(session({
    secret: "secret-key",
    resave: true,
    saveUninitialized: true,
    cookie: {
        httpOnly: true,
        secure: false,
        maxAge: 24 * 60 * 60 * 1000,
        sameSite: 'lax'
    }
}));


app.use(require('./app/middleware/visitCounter'));

// Views
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'app/views'));
app.use(express.static(path.join(__dirname, 'app/public')));

// Middleware para vistas
app.use((req,res,next) => {
    res.locals.user = req.session.userId ? {
        id: req.session.userId,
        username: req.session.username,
        email: req.session.email,
        roles: req.session.roles
    } : null;
    next();
});



// Rutas
require('./app/routes/auth.routes')(app);
require('./app/routes/view.routes')(app);

// Database
const db = require("./app/models");

db.sequelize.sync({force: false}).then(() => {
    console.log('Database synchronized');
});


// Puerto
app.listen(PORT, '0.0.0.0', () => {
});
