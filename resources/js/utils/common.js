// src/assets/js/gaugeWrapper.js
import { Gauge, Donut, BaseDonut, TextRenderer } from '@/js/libs/gauge-wrapper.js';
export { Gauge, Donut, BaseDonut, TextRenderer };

class CommonFunc {
    url = "OmniController.php";

    constructor() { }

    formatMoney(num) {
        return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
    };

    // get the text display with '+' for indicating positive value as net numeral
    // 2020-07-11 update : add money format display
    getNetDisplay(value, format) {
        var displayVal = value;

        if (typeof format === 'undefined' || format === null) {
            // return P/L point 
        } else if (format.toUpperCase() == "FORMATMONEY") {
            displayVal = formatMoney(value);
        }

        return value > 0 ? '+' + displayVal : displayVal;
    };

    // get the color text for norminal
    getNominalTextColor(normainalNet) {
        // console.log("getNominalTextColor function called");
        var colorCodeType = 1; // 1 : green up red down ; 2 : green down red up
        return {
            'text-sgreen-400': colorCodeType == 1 ? normainalNet > 0 : normainalNet < 0,
            'text-red-700': colorCodeType == 1 ? normainalNet < 0 : normainalNet > 0,
            'text-gray-400': normainalNet == 0
        }
    };

    getStatusTextColor(statusText) {
        if (statusText == 'Ready') return 'text-sgreen-400';
        if (statusText == 'Game Over') return 'text-red-700';
        return 'text-gray-400';
    };

    formatDate(milliseconds, haveDay = false) {
        console.log('formatDate milliseconds ? ' + milliseconds);
        const date = new Date(milliseconds);
        console.log(date);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        if (haveDay == false)
            return `${year}/${month}`;

        return `${year}/${month}/${day}`;
    }

    formatTime(milliseconds) {
        const date = new Date(milliseconds);
        const h = String(date.getHours()).padStart(2, '0');
        const m = String(date.getMinutes()).padStart(2, '0');
        const s = String(date.getSeconds()).padStart(2, '0');
        return `${h}:${m}:${s}`;
    }
}


class vwapGauge {
    constructor() {
        this.vwap = 0;
        this.market_price = 0;
        this.ppThreshold = 0;
        this.ppState = '';
        this.vwap_scale_max = 3000;
        console.log("vwapGauge init called");
    }

    setData(marketinfo) {
        this.vwap = marketinfo.vwap;
        this.market_price = marketinfo.nominal;
        this.ppThreshold = marketinfo.ppThreshold;
        this.ppState = marketinfo.ppThresholdState;
        //   console.log("[getData] vwap="+this.vwap+" | market_price="+this.market_price+"| ppThreshold="+this.ppThreshold+"| ppState="+this.ppState);
    }

    vwapscale(orival) {

        if (typeof (orival) == 'undefined') {
            return 0;
        }

        var value = 0;
        var normdiff = Math.abs(this.market_price - this.vwap) - 230;
        normdiff = normdiff > 0 ? normdiff : 0;
        var normoffset = this.market_price < (this.vwap - 230) ? normdiff : 0;
        value = parseInt((orival - (this.vwap - 230) + normoffset) * this.vwap_scale_max / (460 + normdiff));
        //  console.log("orival="+orival + " | vwap = "+this.vwap + " | normdiff = "+normdiff + " | value=" + value + "| diff="+parseInt((orival - (this.vwap - 230))));

        return value > 0 ? (value > this.vwap_scale_max ? this.vwap_scale_max : value) : 0;

    }
    show() {

        var vwap = parseInt(this.vwap);
        var market_price = parseInt(this.market_price);
        var ppThreshold = parseInt(this.ppThreshold);
        var ppState = this.ppState;

        var interval = [vwap - 230, vwap - 200, vwap - 100, vwap - 2, vwap + 2, vwap + 100, vwap + 200, vwap + 230];
        var intcolor = ["#F03E3E", "#FFDD00", "#30B32D", "#0000FF", "#30B32D", "#FFDD00", "#F03E3E"];
        var pindex = interval.length - 1;
        //check ppState
        var ppSize = 3;
        if (ppState.indexOf("*") > 0) {
            ppSize = 20;
        }
        var ppColor = "orange";
        if (ppState.indexOf("R") >= 0) {
            ppColor = "Purple";
        }
        for (var i = 0; i < interval.length; i++) {
            if (ppThreshold < interval[i]) {


                if (i > 0) {
                    var min = ppThreshold - ppSize;
                    var max = ppThreshold + ppSize;
                    if (min <= interval[i - 1]) {
                        min = interval[i - 1] + ppSize;
                    }
                    if (max >= interval[i]) {
                        max = interval[i] - ppSize;
                    }
                    // interval.splice(i,0,ppThreshold-ppSize, ppThreshold+ppSize);
                    interval.splice(i, 0, min, max);
                    intcolor.splice(i, 0, ppColor, intcolor[i - 1]);
                } else {
                    interval.splice(i, 0, ppThreshold - ppSize, ppThreshold + ppSize);
                    intcolor.splice(i, 0, ppColor, intcolor[i]);
                }
                pindex = i;
                break;
            }
        }
        if (pindex == interval.length - 1) { // threshold last
            interval.push(ppThreshold - 2);
            interval.push(ppThreshold + 2);
            intcolor.push(ppColor);
            intcolor.push(intcolor[pindex - 1]);
        }
        //fix vwap guage display problem
        interval.sort(function (a, b) {
            return a - b;
        });


        var zones = [];
        // fine tune interval with norminal
        if (this.market_price < interval[0]) {
            interval[0] = this.market_price;
        }
        if (this.market_price > interval[interval.length - 1]) {
            interval[interval.length - 1] = this.market_price;
        }

        for (var i = 0; i < interval.length; i++) {
            var obj = {};
            obj.strokeStyle = intcolor[i];
            obj.min = this.vwapscale(interval[i]);
            obj.max = this.vwapscale(interval[i + 1]);
            zones.push(obj);
        }

        var opts = {
            angle: -0.04, // The span of the gauge arc
            lineWidth: 0.2, // The line thickness
            radiusScale: 0.83, // Relative radius
            pointer: {
                length: 0.6, // // Relative to gauge radius
                strokeWidth: 0.046, // The thickness
                color: '#FFFFFF' // Fill color
            },
            limitMax: false,     // If false, max value increases automatically if value > maxValue
            limitMin: false,     // If true, the min value of the gauge will be fixed

            strokeColor: '#E0E0E0',  // to see which ones work best for you
            generateGradient: true,
            highDpiSupport: true,     // High resolution support
            staticZones: zones,
            staticLabels: {
                font: "10px sans-serif",  // Specifies font
                labels: [this.vwapscale(vwap - 200), this.vwapscale(vwap - 100), this.vwapscale(vwap + 100), this.vwapscale(vwap + 200)],  // Print labels at these values
                title: [vwap - 200, vwap - 100, vwap + 100, vwap + 200],
                color: "#FFFFFF",  // Optional: Label text color
                fractionDigits: 0  // Optional: Numerical precision. 0=round off.
            },
        };
        var target = document.getElementById('vwapgauge'); // your canvas element
        var gauge = new Gauge(target).setOptions(opts); // create sexy gauge!
        gauge.maxValue = this.vwap_scale_max; // set max gauge value
        gauge.setMinValue(0);  // Prefer setter over gauge.minValue = 0
        gauge.animationSpeed = 1; // set animation speed (32 is default value)
        gauge.set(this.vwapscale(market_price)); // set actual value
    }
    destroy() {
        if (this.gauge) {
            this.gauge.clear();
            this.gauge = null;
        }
    }
}

class btsGauge {
    constructor() {
        this.bts = 0;
        this.btsmax = 0;
        this.btsmin = 0;
        this.btsgauge = null;
        console.log("btsGauge init called");
    }

    setData(marketinfo) {
        this.bts = marketinfo.btsNet;
        this.btsmax = marketinfo.btsCall;
        this.btsmin = marketinfo.btsPut;
    }

    initGauge() {



        // set the gauge type
        var gauge = anychart.gauges.linear();

        // set the layout
        gauge.layout('horizontal');
        gauge.background().enabled(false);

        // create a color scale
        var scaleBarColorScale = anychart.scales.ordinalColor().ranges(
            [
                {
                    from: -2000,
                    to: -1000,
                    color: ['#D81E05', '#EB7A02']
                },
                {
                    from: -1000,
                    to: -700,
                    color: ['#EB7A02', '#FFD700']
                },
                {
                    from: -700,
                    to: 0,
                    color: ['#FFD700', '#FFFF00']
                },
                {
                    from: 0,
                    to: 700,
                    color: ['#FFFF00', '#FFD700']
                },
                {
                    from: 700,
                    to: 1000,
                    color: ['#FFD700', '#CAD70b']
                },
                {
                    from: 1000,
                    to: 2000,
                    color: ['#CAD70b', '#2AD62A']
                }
            ]
        );

        // create a Scale Bar
        var scaleBar = gauge.scaleBar(0);

        // set the height and offset of the Scale Bar (both as percentages of the gauge height)
        scaleBar.width('10%');
        scaleBar.offset('30.5%');

        // use the color scale (defined earlier) as the color scale of the Scale Bar
        scaleBar.colorScale(scaleBarColorScale);

        // add a marker pointer
        var marker = gauge.marker(0);

        // set the offset of the pointer as a percentage of the gauge width
        marker.offset('33.5%');

        // set the color of the marker
        marker.color('#FFFFFF');

        // set the marker type
        marker.type('triangle-up');
        marker.width('25');

        // set the zIndex of the marker
        marker.zIndex(10);


        var markermax = gauge.marker(1);
        markermax.offset('2.5%');
        markermax.color('#277ad7');
        markermax.type('triangle-down');
        markermax.width('25');
        markermax.zIndex(10);

        var markermin = gauge.marker(2);
        markermin.offset('2.5%');
        markermin.color('#c41818');
        markermin.type('triangle-down');
        markermin.width('25');
        markermin.zIndex(10);


        // configure the scale
        var scale = gauge.scale();
        scale.minimum(-2000);
        scale.maximum(2000);
        scale.ticks().interval(2);

        // configure the axis
        var axis = gauge.axis();
        axis.minorTicks(true)
        axis.minorTicks().stroke('#ececec');
        axis.width('1%');
        axis.offset('29.5%');
        axis.orientation('top');

        // format axis labels
        axis.labels().format('{%value}');

        // set paddings
        gauge.padding([0, 50]);

        // set the container id
        gauge.container('btscontainer');

        this.btsgauge = gauge;
    }

    show() {

        var gauge = this.btsgauge;


        // create data
        var data = [this.bts, this.btsmax, this.btsmin];


        // set the data for the gauge
        gauge.data(data);


        // initiate drawing the gauge
        gauge.draw();
    }

    destroy() {
        if (this.btsgauge) {
            this.btsgauge.dispose();
            this.btsgauge = null;
        }
    }
}
class propertiseChart {
    constructor() {
        this.products = [];
        this.doughnutChart = null;
    }

    setData(n_data) {
        // Implementation of setting data (if needed)
    }

    initChart(data, backgroundColor = "black") {
        if (data != null) {
            this.products = data;
            var pie_data = [];
            // Transform the data for chart
            this.products.forEach(row => {
                pie_data.push({ x: row.x, value: row.value });
            });
        }

        // Initialize chart with new chart data
        var chart = anychart.pie(pie_data);

        this.doughnutChart = chart;

        chart.innerRadius("50%");
        chart.background(backgroundColor);  // Use the passed backgroundColor
    }

    show() {
        var target = document.getElementById('propertiesChartView');

        if (target) { // Check if the target element exists
            target.childNodes.forEach(ele => {
                ele.remove();
            });

            var chart = this.doughnutChart;
            chart.container("propertiesChartView");
            // Initiate drawing the gauge
            chart.draw();
        } else {
            console.error("Target element 'propertiesChartView' not found.");
        }
    }
}

class propertiseBTCChart {
    constructor() {
        this.products = [];
        this.btcchart = null;
    }

    setData(n_data) {

    }

    initChart(data) {
        // create a data set
        var chartdata = anychart.data.set(data);

        // map the data
        var seriesData_1 = chartdata.mapAs({ x: 0, value: 1 });
        var seriesData_2 = chartdata.mapAs({ x: 0, value: 2 });
        var seriesData_3 = chartdata.mapAs({ x: 0, value: 3 });

        var s1_color = "#00cc99";
        var s2_color = "#0066cc";
        var s3_color = "#AA0099";

        // create a chart
        var chart = anychart.area();

        // set the interactivity mode
        chart.interactivity().hoverMode("single");

        // create the first series, set the data and name
        var series1 = chart.area(seriesData_1);
        series1.name("牛熊");

        // configure the visual settings of the first series
        series1.normal().fill(s1_color, 0.3);
        series1.hovered().fill(s1_color, 0.1);
        series1.selected().fill(s1_color, 0.5);
        series1.normal().stroke(s1_color, 1, "10 5", "round");
        series1.hovered().stroke(s1_color, 2, "10 5", "round");
        series1.selected().stroke(s1_color, 4, "10 5", "round");

        // create the second series, set the data and name  
        var series2 = chart.area(seriesData_2);
        series2.name("期貨");

        // configure the visual settings of the second series
        series2.normal().fill(s2_color, 0.3);
        series2.hovered().fill(s2_color, 0.1);
        series2.selected().fill(s2_color, 0.5);
        series2.normal().hatchFill("forward-diagonal", s2_color, 1, 15);
        series2.hovered().hatchFill("forward-diagonal", s2_color, 1, 15);
        series2.selected().hatchFill("forward-diagonal", s2_color, 1, 15);
        series2.normal().stroke(s2_color);
        series2.hovered().stroke(s2_color, 2);
        series2.selected().stroke(s2_color, 4);

        // create the second series, set the data and name  
        var series3 = chart.area(seriesData_3);
        series3.name("流動資金");

        // configure the visual settings of the second series
        series3.normal().fill(s3_color, 0.3);
        series3.hovered().fill(s3_color, 0.1);
        series3.selected().fill(s3_color, 0.5);
        series3.normal().hatchFill("forward-diagonal", s3_color, 1, 15);
        series3.hovered().hatchFill("forward-diagonal", s3_color, 1, 15);
        series3.selected().hatchFill("forward-diagonal", s3_color, 1, 15);
        series3.normal().stroke(s3_color);
        series3.hovered().stroke(s3_color, 2);
        series3.selected().stroke(s3_color, 4);

        // set the chart title
        // chart.title("Area Chart: Appearance");

        // set the titles of the axes
        chart.xAxis().title("按月(Month)");
        chart.yAxis().title("總持貨價值, $");

        this.btcchart = chart;
    }

    show() {
        var target = document.getElementById('propertiesChartView');
        target.childNodes.forEach(ele => {
            ele.remove();
        });

        var chart = this.btcchart;
        chart.container("propertiesChartView");
        chart.background("black");
        // initiate drawing the gauge
        chart.draw();
    }
}



// export default CommonFunc
export {
    CommonFunc,
    vwapGauge,
    btsGauge,
    propertiseChart,
    propertiseBTCChart
}