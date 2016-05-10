$(init);

function init() {
    $('.hasdatepicker').datepicker({
        format: "dd-mm-yyyy"
    });
}

function createChart(dates, clicks, unique, id, startdate, enddate) {
    var between;
    if (startdate == "") {
        between = ""
    } else {
        between = " between " + startdate + " and " + enddate;
    }

    $('#container').highcharts({
        chart: {
            type: 'areaspline'
        },
        title: {
            text: 'Clicks on url #' + id + between
        },
        legend: {
            layout: 'vertical',
            align: 'left',
            verticalAlign: 'top',
            x: 150,
            y: 100,
            floating: true,
            borderWidth: 1,
            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
        },
        xAxis: {
            categories: dates
        },
        yAxis: {
            title: {
                text: 'Clicks'
            }
        },
        tooltip: {
            shared: true,
            valueSuffix: ' Clicks'
        },
        credits: {
            enabled: false
        },
        plotOptions: {
            areaspline: {
                fillOpacity: 0.5
            }
        },
        series: [{
            name: 'Total',
            data: clicks,
            backgroundColor: "#A0A"
        }, {
            name: 'Unique',
            data: unique
        }]
    });
}