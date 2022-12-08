
// 性別性別統計圖Chart
$(function () {
    $('#container0').highcharts({
        lang: {
            downloadJPEG: dJPEG,
            downloadPDF:dPDF,
            downloadPNG:dPNG,
            downloadSVG:dSVG,
            printChart:pChart
        },
        credits: {
            enabled:false
        },
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
            text: ''
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Browser share',
            data: [
                [female,   parseInt(genderP.F)],
                [male,       parseInt(genderP.M)],
                [not_mark,       parseInt(genderP.NOT_MARKED)]
            ]
        }]
    });
});

// 年齡統計圖Chart
$(function () {
    $('#container1').highcharts({
        lang: {
            downloadJPEG: dJPEG,
            downloadPDF:dPDF,
            downloadPNG:dPNG,
            downloadSVG:dSVG,
            printChart:pChart
        },
        credits: {
            enabled:false
        },
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
            text: ''
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Browser share',
            data: [
            	['10'+year_old+year_under, parseInt(ageP.A)],
            	['10~15'+year_old, parseInt(ageP.B)],
            	['16~20'+year_old, parseInt(ageP.C)],
            	['21~25'+year_old, parseInt(ageP.D)],
            	['26~30'+year_old, parseInt(ageP.E)],
            	['31~35'+year_old, parseInt(ageP.F)],
            	['35~40'+year_old, parseInt(ageP.G)],
            	['41'+year_old+year_above, parseInt(ageP.H)],
                [not_mark , parseInt(ageP.NOT_MARKED)]
            ]
        }]
    });
});

// 角色統計圖Chart
$(function () {
    $('#container2').highcharts({
        lang: {
            downloadJPEG: dJPEG,
            downloadPDF:dPDF,
            downloadPNG:dPNG,
            downloadSVG:dSVG,
            printChart:pChart
        },
        credits: {
            enabled:false
        },
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
            text: ''
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Browser share',
            data: [
                [student, parseInt(statusP.S)],
                [at_work, parseInt(statusP.W)],
                [not_mark, parseInt(statusP.NOT_MARKED)]
            ]
        }]
    });
});

// 學歷統計圖Chart
$(function () {
    $('#container3').highcharts({
        lang: {
            downloadJPEG: dJPEG,
            downloadPDF:dPDF,
            downloadPNG:dPNG,
            downloadSVG:dSVG,
            printChart:pChart
        },
        credits: {
            enabled:false
        },
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
            text: ''
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Browser share',
            data: [
                [elementary_school, parseInt(educationP.P)],
                [junior_high_school, parseInt(educationP.H)],
                [high_school, parseInt(educationP.S)],
                [university, parseInt(educationP.U)],
                [masters_degree, parseInt(educationP.M)],
                [doctoral_degree, parseInt(educationP.D)],
                [Op, parseInt(educationP.O)],
                [not_mark, parseInt(educationP.NOT_MARKED)]
            ]
        }]
    });
});



// 身分統計圖Chart
$(function () {
    $('#container4').highcharts({
        lang: {
            downloadJPEG:dJPEG,
            downloadPDF:dPDF,
            downloadPNG:dPNG,
            downloadSVG:dSVG,
            printChart:pChart
        },
        credits: {
            enabled:false
        },
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
            text: ''
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Browser share',
            data: [
                [student, parseInt(roleP.STD)],
                [teacher, parseInt(roleP.TEA)],
                [teach_asis, parseInt(roleP.ASIS)],
                [teach_instr, parseInt(roleP.INSTR)]
            ]
        }]
    });
});


// 來源地區(國家)統計圖Chart
$(function () {
    $('#container5').highcharts({
        lang: {
            downloadJPEG: dJPEG,
            downloadPDF:dPDF,
            downloadPNG:dPNG,
            downloadSVG:dSVG,
            printChart:pChart
        },
        credits: {
            enabled:false
        },
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
            text: ''
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Browser share',
            data: [
                [TWp, parseInt(countryP.TW)],
                [CHp, parseInt(countryP.CH)],
                [JAp, parseInt(countryP.JA)],
                [INp, parseInt(countryP.IN)],
                [USp, parseInt(countryP.US)],
                [ASp, parseInt(countryP.AS)],
                [Op, parseInt(countryP.O)],,
                [not_mark, parseInt(countryP.NOT_MARKED)]
            ]
        }]
    });
});

/**
 * Sample
 *
 */
// $(function () {
//     $('#container4').highcharts({
//         credits: {
//             enabled:false
//         },
//         chart: {
//             plotBackgroundColor: null,
//             plotBorderWidth: null,
//             plotShadow: false
//         },
//         title: {
//             text: ''
//         },
//         tooltip: {
//             pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
//         },
//         plotOptions: {
//             pie: {
//                 allowPointSelect: true,
//                 cursor: 'pointer',
//                 dataLabels: {
//                     enabled: true,
//                     format: '<b>{point.name}</b>: {point.percentage:.1f} %',
//                     style: {
//                         color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
//                     }
//                 }
//             }
//         },
//         series: [{
//             type: 'pie',
//             name: 'Browser share',
//             data: [
//                 ['Firefox',   45.0],
//                 ['IE',       26.8],
//                 {
//                     name: 'Chrome',
//                     y: 12.8,
//                     sliced: true,
//                     selected: true
//                 },
//                 ['Safari',    8.5],
//                 ['Opera',     6.2],
//                 ['Others',   0.7]
//             ]
//         }]
//     });
// });