module.exports = (sequelize, Sequelize) => {
  const News = sequelize.define("news", {
    idNews: {
      type: Sequelize.INTEGER,
      primaryKey: true,
      autoIncrement: true
    },
    Titulo: {
      type: Sequelize.STRING
    },
    Resumen: {
      type: Sequelize.TEXT
    },
    Imagen: {
      type: Sequelize.STRING
    },
    Autor: {
      type: Sequelize.STRING
    },
    Fuente: {
      type: Sequelize.STRING
    },
    Enlace: {
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

  return News;
};