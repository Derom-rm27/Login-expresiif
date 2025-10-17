const jwt = require("jsonwebtoken");
const config = require("../config/auth.config.js");
const db = require("../models");
const User = db.user;

verifyToken = (req, res, next) => {
  let token = req.session.token;

  if (!token) {
    return res.redirect('/login');
  }

  jwt.verify(token,
             config.secret,
             (err, decoded) => {
              if (err) {
                return res.redirect('/login');
              }
              req.userId = decoded.id;
              next();
             });
};

isAdmin = async (req, res, next) => {
  try {
    const user = await User.findByPk(req.userId);
    const roles = await user.getRoles();

    for (let i = 0; i < roles.length; i++) {
      if (roles[i].name === "admin") {
        return next();
      }
    }
  } catch (error) {
    return res.status(500).render('error', {
      title: 'Error',
      message: "Unable to validate User role!"
    });
  }
};

isModerator = async (req, res, next) => {
  try {
    const user = await User.findByPk(req.userId);
    const roles = await user.getRoles();

    for (let i = 0; i < roles.length; i++) {
      if (roles[i].name === "moderator") {
        return next();
      }
    }

  } catch (error) {
    return res.status(500).render('error', {
      title: 'Error',
      message: "Unable to validate Moderator role!"
    });
  }
};

const authJwt = {
  verifyToken,
  isAdmin,
  isModerator,
};
module.exports = authJwt;