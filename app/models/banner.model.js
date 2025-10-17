module.exports = (sequelize, Sequelize) => {
  const Banner = sequelize.define("banners", {
    idBanner: {
      type: Sequelize.INTEGER,
      primaryKey: true,
      autoIncrement: true
    },
    Titulo: {
      type: Sequelize.STRING
    },
    Descripción: {
      type: Sequelize.TEXT
    },
    Enlace: {
      type: Sequelize.STRING
    },
    Imagen: {
      type: Sequelize.STRING
    },
    estado: {
      type: Sequelize.BOOLEAN,
      defaultValue: true
    },
    userId: {
      type: Sequelize.INTEGER
    }
  });

  return Banner;
};