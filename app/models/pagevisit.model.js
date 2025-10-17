module.exports = (sequelize, Sequelize) => {
  const PageVisit = sequelize.define("page_visit", {
    id: {
      type: Sequelize.INTEGER,
      primaryKey: true,
      autoIncrement: true
    },
    ip_address: {
      type: Sequelize.STRING(45)
    },
    user_agent: {
      type: Sequelize.TEXT
    },
    operating_system: {
      type: Sequelize.STRING(100)
    },
    browser: {
      type: Sequelize.STRING(100)
    },
    visit_time: {
      type: Sequelize.DATE,
      defaultValue: Sequelize.NOW
    }
  });

  return PageVisit;
};