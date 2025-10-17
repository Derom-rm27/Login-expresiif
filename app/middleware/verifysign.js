const db = require("../models");
const ROLES = db.ROLES;
const User = db.user;

checkDuplicateUsernameOrEmail = async (req, res, next) => {
  try {
    // Username
    let user = await User.findOne({
      where: {
        username: req.body.username
      }
    });

    if (user) {
      return res.render('auth/register', {
        title: 'Registrarse',
        error: "El nombre de usuario ya está en uso!"
      });
    }

    // Email
    user = await User.findOne({
      where: {
        email: req.body.email
      }
    });

    if (user) {
      return res.render('auth/register', {
        title: 'Registrarse',
        error: "El email ya está en uso!"
      });
    }

    next();
  } catch (error) {
    return res.render('auth/register', {
      title: 'Registrarse',
      error: "Error al validar los datos!"
    });
  }
};

checkRolesExisted = (req, res, next) => {
  if (req.body.roles) {
    for (let i = 0; i < req.body.roles.length; i++) {
      if (!ROLES.includes(req.body.roles[i])) {
        return res.render('auth/register', {
          title: 'Registrarse',
          error: "El rol no existe: " + req.body.roles[i]
        });
      }
    }
  }
  
  next();
};

const verifysign = {
  checkDuplicateUsernameOrEmail,
  checkRolesExisted
};

module.exports = verifysign;