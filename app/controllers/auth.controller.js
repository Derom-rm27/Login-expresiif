const db = require('../models');
const config = require('../config/auth.config')

const User = db.user;
const Role = db.role;

const Op = db.Sequelize.Op;

const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');

exports.signup = async (req, res)=>{
//Guardar usuario en la base de datos
try{

    const user = await User.create({

        username : req.body.username,
        email: req.body.email,
        password: bcrypt.hashSync(req.body.password, 8),
    });

    if (req.body.roles){

        const roles = await Role.findAll({

            where:{
                name:{
                    [Op.or]: req.body.roles,
                },
            },
        });

        await user.setRoles(roles);
        res.redirect('/login?message=Usuario registrado correctamente');

    }else{

        //El usuario tiene el rol 1
        await user.setRoles([1]);
        res.redirect('/login?message=Usuario registrado correctamente');

    }

}catch(error){

    res.render('auth/register', {
        title: 'Registrarse',
        error: error.message
    });

}
};

exports.signin = async (req, res) => {
    try {
        const user = await User.findOne({
            where: {
                username: req.body.username,
            },
        });

        if (!user) {

            return res.render('auth/login', {
                title: 'Iniciar Sesión',
                error: 'Usuario no encontrado'
            });
        }

        const passwordValid = bcrypt.compareSync(
            req.body.password,
            user.password
        );

        if (!passwordValid) {

            return res.render('auth/login', {
                title: 'Iniciar Sesión',
                error: 'Contraseña inválida'
            });
        }


        const token = jwt.sign({ id: user.id },
            config.secret,
            {
                algorithm: 'HS256',
                expiresIn: 86400,
            }
        );

        let authorities = [];
        const roles = await user.getRoles();
        for (let i = 0; i < roles.length; i++) {
            authorities.push("ROLE_" + roles[i].name.toUpperCase());
        }


        req.session.token = token;
        req.session.userId = user.id;
        req.session.username = user.username;
        req.session.email = user.email;
        req.session.roles = authorities;


        // Redirigir según el rol
        if (authorities.includes("ROLE_ADMIN")) {

            return res.redirect('/admin-dashboard');

        } else if (authorities.includes("ROLE_MODERATOR")) {

            return res.redirect('/moderator-dashboard');

        } else if (authorities.includes("ROLE_USER")) {

            return res.redirect('/user-dashboard');

        } else {

            return res.redirect('/profile');
        }

    } catch (error) {
        console.error("ERROR:", error);
        res.render('auth/login', {
            title: 'Iniciar Sesión',
            error: error.message
        });
    }
};

///Cerrar sesión


exports.signout = async (req,res)=>{

try{

    req.session = null;
    res.redirect('/login');

}catch(err){
    
    res.redirect('/');

}

}