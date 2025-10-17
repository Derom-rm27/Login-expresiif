const {verifysign} = require('../middleware');
const controller = require('../controllers/auth.controller.js')

module.exports = function(app){

    app.post(
        '/auth/signup',
        [
            verifysign.checkDuplicateUsernameOrEmail,
            verifysign.checkRolesExisted,
        ],
        controller.signup
    );

    app.post('/auth/signin', controller.signin);
    app.post('/auth/signout', controller.signout);

    app.get('/login', (req, res) => {
        res.render('auth/login', {
            title: 'Iniciar Sesión',
            message: req.query.message || null,
            error: null
        });
    });

    app.get('/register', (req, res) => {
        res.render('auth/register', {
            title: 'Registrarse',
            message: req.query.message || null,
            error: null,
            formData: {}
        });
    });
};