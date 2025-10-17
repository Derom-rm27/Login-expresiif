const config = require("../config/db.config");

const Sequelize = require("sequelize");
const sequelize = new Sequelize(
  config.DB,
  config.USER,
  config.PASSWORD,
  {
    host: config.HOST,
    dialect: config.dialect,
    pool: {
      max: config.pool.max,
      min: config.pool.min,
      acquire: config.pool.acquire,
      idle: config.pool.idle
    }
  }
);

const db = {};

db.Sequelize = Sequelize;
db.sequelize = sequelize;

db.user = require("../models/user.model.js")(sequelize, Sequelize);
db.role = require("../models/role.model.js")(sequelize, Sequelize);
db.banner = require("../models/banner.model.js")(sequelize, Sequelize);
db.news = require('./news.model.js')(sequelize, Sequelize);


db.role.belongsToMany(db.user, {
  through: "user_roles"
});
db.user.belongsToMany(db.role, {
  through: "user_roles"
});

db.user.hasMany(db.banner, {
  foreignKey: "userId",
  as: "banners"
});

db.banner.belongsTo(db.user, {
  foreignKey: "userId", 
  as: "user"
});

db.user.hasMany(db.news, {
  foreignKey: "userId",
  as: "news"
});

db.news.belongsTo(db.user, {
  foreignKey: "userId", 
  as: "user"
});

db.ROLES = ["user", "admin", "moderator"];

db.pagevisit = require("../models/pagevisit.model.js")(sequelize, Sequelize);

module.exports = db;