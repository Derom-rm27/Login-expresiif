const db = require('../models');

exports.visitReport = async (req, res) => {
    try {
        const totalVisits = await db.pagevisit.count();
        const todayVisits = await db.pagevisit.count({
            where: {
                visit_time: {
                    [db.Sequelize.Op.gte]: new Date(new Date() - 24 * 60 * 60 * 1000)
                }
            }
        });

        const visitsByIP = await db.pagevisit.findAll({
            attributes: [
                'ip_address',
                [db.Sequelize.fn('COUNT', db.Sequelize.col('id')), 'visit_count'],
                [db.Sequelize.fn('MAX', db.Sequelize.col('visit_time')), 'last_visit']
            ],
            group: ['ip_address'],
            order: [[db.Sequelize.literal('visit_count'), 'DESC']],
            limit: 10
        });

        const statsByOS = await db.pagevisit.findAll({
            attributes: [
                'operating_system',
                [db.Sequelize.fn('COUNT', db.Sequelize.col('id')), 'count']
            ],
            group: ['operating_system'],
            order: [[db.Sequelize.literal('count'), 'DESC']]
        });

        const statsByBrowser = await db.pagevisit.findAll({
            attributes: [
                'browser',
                [db.Sequelize.fn('COUNT', db.Sequelize.col('id')), 'count']
            ],
            group: ['browser'],
            order: [[db.Sequelize.literal('count'), 'DESC']]
        });

        res.render('pages/visit-report', {
            title: 'Reporte de Visitas - Admin',
            user: {
                id: req.session.userId,
                username: req.session.username,
                email: req.session.email,
                roles: req.session.roles
            },
            totalVisits: totalVisits,
            todayVisits: todayVisits,
            visitsByIP: visitsByIP,
            statsByOS: statsByOS,
            statsByBrowser: statsByBrowser
        });

    } catch (error) {
        console.error('Error en visitReport:', error);
        res.redirect('/admin-dashboard?error=Error+al+cargar+reporte');
    }
};