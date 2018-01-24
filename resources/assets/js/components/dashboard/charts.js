import _ from 'lodash';
import moment from 'moment';

import Req from 'req';
import util from './util';

export default {

    /**
     * Returns the base configuration for bar chart for the planner
     * @returns {{type: string, options: {scaleBeginAtZero: boolean, responsive: boolean, maintainAspectRatio: boolean, scaleShowGridLines: boolean, scaleGridLineColor: string, scaleGridLineWidth: number, scales: {xAxes: *[], yAxes: *[]}, tooltips: {mode: string}}}}
     */
    plannerBaseConfig(){
        return {
            type: 'bar',
            options: {
                scaleBeginAtZero: true,
                responsive: true,
                maintainAspectRatio: true,
                scaleShowGridLines: true,
                scaleGridLineColor: "rgba(0,0,0,.02)",
                scaleGridLineWidth: 1,
                scales: {
                    xAxes: [{
                        stacked: true,
                    }],
                    yAxes: [{
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            callback: (value, index, values) => {
                                return _.floor(value) === value ? value : null;
                            }
                        }
                    }]
                },
                tooltips:{
                    mode: 'index'
                },
            }
        };
    },

    /**
     * Returns config for doughnut chart with certificate types.
     * @param certTypesStatsAll
     * @param certTypesStats
     * @returns {{type: string, data: {datasets: *[], labels: string[]}, options: {responsive: boolean, legend: {position: string}, title: {display: boolean, text: string}, animation: {animateScale: boolean, animateRotate: boolean}}}}
     */
    certTypesConfig(certTypesStatsAll, certTypesStats){
        return {
            type: 'doughnut',
            data: {
                datasets: [
                    {
                        data: certTypesStatsAll,
                        backgroundColor: [util.chartColors[0], util.chartColors[1], util.chartColors[2]],
                        label: 'All issued certificates (CT)'
                    },
                    {
                        data: certTypesStats,
                        backgroundColor: [util.chartColors[0], util.chartColors[1], util.chartColors[2]],
                        label: 'Certificates on watched servers'
                    }],
                labels: [
                    'Let\'s Encrypt',
                    'Managed by CDN/ISP',
                    'Long validity'
                ]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Certificate types'
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        };
    },

    /**
     * Returns the configuration for the doughnut graph for 4week renewal
     * @param dataset
     */
    week4renewConfig(dataset){
        return {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: dataset,
                    backgroundColor: [
                        util.chartColors[12],
                        util.chartColors[3],
                        util.chartColors[1],
                        util.chartColors[0],
                        util.chartColors[2],
                    ],
                    label: 'Renewals in 4 weeks'
                }],
                labels: [
                    "expired",
                    "0-7 days",
                    "8-14 days",
                    "15-21 days",
                    "22-28 days"
                ]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'right',
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        };
    },

    /**
     * Certificate issuer bar chart configuration
     * @param allIssuerUnz
     * @param tlsIssuerUnz
     * @returns {{type: string, data: {datasets: *[], labels: *}, options: {scaleBeginAtZero: boolean, responsive: boolean, legend: {position: string}, title: {display: boolean, text: string}, animation: {animateScale: boolean, animateRotate: boolean}}}}
     */
    certIssuerConfig(allIssuerUnz, tlsIssuerUnz){
        return {
            type: 'horizontalBar',
            data: {
                datasets: [
                    {
                        data: tlsIssuerUnz[1],
                        backgroundColor: util.chartColors[0],
                        //backgroundColor: Req.takeMod(util.chartColors, tlsIssuerUnz[0].length),
                        label: 'Detected on servers'
                    },
                    {
                        data: allIssuerUnz[1],
                        backgroundColor: util.chartColors[2],
                        //backgroundColor: Req.takeMod(util.chartColors, allIssuerUnz[0].length),
                        label: 'From CT logs only'
                    }],
                labels: allIssuerUnz[0]
            },
            options: {
                scaleBeginAtZero: true,
                responsive: true,
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Certificate issuers'
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        };
    },

    /**
     * Returns configuration for certificate domains bar chart
     * @param dataset
     * @param titleText
     * @returns {{type: string, data: {datasets: *[], labels: Array}, options: {scaleBeginAtZero: boolean, responsive: boolean, legend: {position: string}, title: {display: boolean, text: *|string}, animation: {animateScale: boolean, animateRotate: boolean}}}}
     */
    certDomainsConfig(dataset, titleText){
        return {
            type: 'bar',
            data: {
                datasets: [
                    {
                        data: dataset[0][1],
                        backgroundColor: util.chartColors[0],
                        //backgroundColor: Req.takeMod(util.chartColors, unzipped[0][1].length),
                        label: 'Watched servers'
                    },
                    {
                        data: dataset[1][1],
                        backgroundColor: util.chartColors[2],
                        //backgroundColor: Req.takeMod(util.chartColors, unzipped[1][1].length),
                        label: 'All issued certificates (CT)'
                    }],
                labels: _.map(dataset[0][0], x => util.getCountCategoryLabel(x))
            },
            options: {
                scaleBeginAtZero: true,
                responsive: true,
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: titleText || 'All watched domains (server names)'
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        };
    },

}

