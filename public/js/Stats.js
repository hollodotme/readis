$(document).ready(function () {
    google.charts.load('current', {'packages': ['corechart']});
    google.charts.setOnLoadCallback(drawVisualization);

    function drawVisualization() {
        var clientstats = new google.visualization.LineChart(document.getElementById('clientstats'));
        var iostats = new google.visualization.LineChart(document.getElementById('iostats'));
        var lastClientsTimePoint = 0;
        var lastIoTimePoint = 0;
        var clientData = new google.visualization.DataTable();
        var ioData = new google.visualization.DataTable();
        var source = null;

        clientData.addColumn('string', 'Time');
        clientData.addColumn('number', 'Clients');

        ioData.addColumn('string', 'Time');
        ioData.addColumn('number', 'Input');
        ioData.addColumn('number', 'Output');

        var clientOptions = {
            title: 'Connected clients',
            width: 529,
            height: 400,
            hAxis: {viewWindow: {min: 0, max: 10}},
            animation: {
                duration: 250,
                easing: 'in'
            },
            chartArea: {
                left: 40,
                right: 90
            }
        };
        var ioOptions = {
            title: 'Input / Output in KB/sec.',
            width: 529,
            height: 400,
            hAxis: {viewWindow: {min: 0, max: 10}},
            animation: {
                duration: 250,
                easing: 'in'
            },
            chartArea: {
                left: 40,
                right: 90
            }
        };

        var button = document.getElementById('b1');

        $(button).on('click', startMonitor);

        function startMonitor(e) {
            e.preventDefault();
            var eventSourceUrl = $(button).data('event-source');
            $(button).html('<span class="fa fa-stop"></span> Stop monitor');
            $(button).off().on('click', stopMonitor);

            if (!!window.EventSource) {
                source = new EventSource(eventSourceUrl);
                source.addEventListener('clientsConnected', function (e) {
                    lastClientsTimePoint += 0.5;
                    if (clientData.getNumberOfRows() > 10) {
                        clientOptions.hAxis.viewWindow.min += 1;
                        clientOptions.hAxis.viewWindow.max += 1;
                    }
                    console.log('clientsConnected: ' + parseInt(e.data));
                    clientData.addRow([lastClientsTimePoint.toString(), parseInt(e.data)]);
                    clientstats.draw(clientData, clientOptions);
                }, false);

                source.addEventListener('ioKbPerSecond', function (e) {
                    lastIoTimePoint += 0.5;
                    if (ioData.getNumberOfRows() > 10) {
                        ioOptions.hAxis.viewWindow.min += 1;
                        ioOptions.hAxis.viewWindow.max += 1;
                    }
                    console.log('I/O: ' + e.data);
                    var io = e.data.split(':');
                    ioData.addRow([lastClientsTimePoint.toString(), parseFloat(io[0]), parseFloat(io[1])]);
                    iostats.draw(ioData, ioOptions);
                }, false);

                source.addEventListener('open', function () {
                    console.log('Event Source opened.');
                }, false);

                source.addEventListener('beginOfStream', function () {
                    console.log('Stream started.');
                }, false);

                source.addEventListener('endOfStream', function () {
                    stopMonitor();
                    console.log('Stream ended.');
                }, false);

                source.addEventListener('error', function (e) {
                    if (e.readyState === EventSource.CLOSED) {
                        console.log('Event Source closed.');
                        source.close();
                    }
                }, false);
            }
            else {
                alert('No event source available. Please use another, modern browser!');
            }
        }

        function stopMonitor() {
            source.close();
            $(button).html('<span class="fa fa-play"></span> Start monitor');
            $(button).off().on('click', startMonitor);
        }

        clientstats.draw(clientData, clientOptions);
        iostats.draw(ioData, ioOptions);
    }
});
