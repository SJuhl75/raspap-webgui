    <!-- Plotly.js 3D Definitions -->
    <!--script type="text/javascript"-->
    
    
    
    // NOTE USE https://developer.mozilla.org/de/docs/Web/JavaScript/Reference/Global_Objects/Array/map
    // NOTE USE https://www.w3schools.com/colors/colors_picker.asp

    /* NOTE PROGRESS BAR ... für Messung / Aktuaisierung Ladebalken
            var elem = document.getElementById("myBar");
            var width = 0;
            var id = setInterval(frame, 10);
            function frame() {
                if (width == 100) {
                    clearInterval(id);
                } else {
                    width++;
                    elem.style.width = width + '%';
                }
            }*/        
    
    // NOTE WORKING Static Ratio and satellite count, limited to three hours
    // NOTE DATA Fields: Timestamp,X,Y,Z,UTMZ,UTMR,UTMH,ALT,R95,PDOP,SDX,SDY,SDZ,Q,NS,RATIO
    // function unpack(rows, key) { return rows.map(function(row) { return row[key]; }); }


/*        const date = Date.now();
        let currentDate = null;
        do { currentDate = Date.now(); } while (currentDate - date < 300); */

/*      Plotly.d3.csv('rtksvr.csv', function(data) { processData(data) } );
      function processData(allRows) {
            var last   = allRows.length-1;
            var time   = new Date(Date.parse(allRows[last]['Timestamp'])); 
            var update = { x: [[time],[time]], y: [[R95],[RATIO]] }
            var olderTime  = time.setMinutes(time.getMinutes() - timespan);
            var futureTime = time.setMinutes(time.getMinutes() + timespan); */


    
    Plotly.d3.csv('rtksvr.csv', function(err, rows) {
      function unpack(rows, key) { var DataTime = Date.parse(rows[rows.length-1]['Timestamp']);
                                   return rows.map(function(row) { var RowTime = Date.parse(row['Timestamp']); 
                                                                   if ((DataTime - RowTime) < 3*3600000 ) return row[key]; 
                                                                   }).filter(Boolean);
                                   }
      var data = [{ x: unpack(rows, 'Timestamp'),
                    y: unpack(rows, 'NS'),
                    type: 'scatter',
                    line: { color: 'rgb(23, 190, 207)', width: 0.8, opacity: 0.1, },
                    name: 'Sat count', fill: 'tozeroy', fillcolor: 'rgba(230, 255, 255,0.6)' },
                  { x: unpack(rows, 'Timestamp'),
                    y: unpack(rows, 'RATIO'),
                    type: 'scatter',
                    line: { color: 'blue', width: 2 },
                    opacity: 1.0, name: 'Ratio', yaxis: 'y2' }];
          
      var layout = {autosize: true, height: 350, margin: {l: 50, t: 60, b:30 },
                    title: {text: 'Ratio factor of ambiguity validation and number of satellites used',
                    font: { size: 20, color: 'blue' }, xref: 'container', x: 0.0, xanchor: 'left', yref: 'container', y: 0.975, yanchor: 'top' },
                    xaxis: { type: 'date', tickformat: "%X", automargin: true,
                             rangeselector: {buttons: [{ count: 1,  label: '1d',  step: 'day', stepmode: 'backward' },
                                                       { count: 10, label: '10d', step: 'day', stepmode: 'backward' },
                                                       { step: 'all'} ]}, },
                    yaxis: { title: 'Sat count   ', type: 'linear', zeroline: false, side: 'right', rangemode: 'tozero', automargin: true },
                    yaxis2: { title: 'Ratio', yanchor: 'top', type: 'log', zeroline: false, side: 'left', overlaying: 'y', automargin: true } };
      Plotly.react('Chart1', data, layout); 
      // Enable Tab if data available
      $('a[href="#signall"]').html('<span class="" role="status" aria-hidden="false"><?php echo _("RTK Quality"); ?></span>').removeClass('disabled');    
    });

    // NOTE Time series Position, stacked
    Plotly.d3.csv('rtksvr.csv', function(err, rows) {
      function unpack(rows) { var DataTime = Date.parse(rows[rows.length-1]['Timestamp']);
                              var LastVal = [rows[rows.length-1]['UTMH'], rows[rows.length-1]['UTMR'], rows[rows.length-1]['ALT'] ];
                              const ts=[], utmh=[], utmr=[], alt=[];
                              rows.forEach((row) => { var RowTime = Date.parse(row['Timestamp']); 
                                                      if ((DataTime - RowTime) < 8*3600000 ) {
                                                         ts.push(row['Timestamp']);
                                                         utmh.push((LastVal[0]-row['UTMH'])*1000);
                                                         utmr.push((LastVal[1]-row['UTMR'])*1000);
                                                         alt.push(( LastVal[2]-row['ALT'] )*1000);
                                                      }});
                              return { ts, utmh, utmr, alt }; } // (LastVal-row[key])*1000
      const {ts, utmh, utmr, alt} = unpack(rows);
  
      var trace1 = { x: ts,
                     y: utmh, // UTMH - North
                     name: 'UTMH', 
                     line: {color: 'blue', width: 0.8 },
                     type: 'scatter' };
  
      var trace2 = { x: ts,
                     y: utmr, // UTMR - East
                     name: 'UTMR',color:'#5C7DDE',
                     line: {color: 'blue', width: 0.8 },
                     xaxis: 'x',
                     yaxis: 'y2',
                     type: 'scatter' };
  
      var trace3 = { x: ts, 
                     y: alt, // ALT - Altitude
                     name: 'ALT',color:'#5C7DDE',
                     line: {color: 'blue', width: 0.8 },
                     xaxis: 'x',
                     yaxis: 'y3',
                     type: 'scatter' };
  
      var data = [trace1, trace2, trace3];
  
      var layout = { showlegend: false, height: 500, margin: {l: 70, t: 60, b:20, r:5 },
                     grid: { rows: 3, columns: 1, shared_xaxes: true, 
                     subplots:[['xy'],['xy2'],['xy3']],
                     roworder:'top to bottom' },
                     title: {text: 'Position Time Series in ETRS89/UTM',
                     font: { size: 20, color: 'blue' }, xref: 'container', x: 0.0, xanchor: 'left', yref: 'container', y: 0.975, yanchor: 'top' },
                     xaxis: {tickformat: "%X" },
                     yaxis:  {title: {text: 'North<br>[mm]', font: { color:'#5C7DDE' } }, tickformat: '.1f', range: [-15.00, +15.00], autorange: false},
                     yaxis2: {title: {text: 'East<br>[mm]' , font: { color:'#5C7DDE' } }, tickformat: '.1f', range: [-15.00, +15.00], autorange: false},
                     yaxis3: {title: {text: 'Up<br>[mm]'   , font: { color:'#5C7DDE' } }, tickformat: '.1f', range: [-30.00, +30.00], autorange: false}
                     };

      Plotly.newPlot('Chart2', data, layout); 
      // Enable Tab if data available
      $('a[href="#positionss"]').html('<span class="" role="status" aria-hidden="false"><?php echo _("Position"); ?></span>').removeClass('disabled');
    }); // End of inline function

    // NOTE Satellite ephemeris observations
    var r=1;
    var obsts=24;
    Plotly.d3.csv('strobs.csv', function(err, rows) { 
      function unpack(rows) { var DataTime = Date.parse(rows[rows.length-1]['Timestamp']);
                              const ts=[], va=[], vb=[], vc=[], vd=[], ve=[], vf=[];  // Timestamp,az,el,cno,PRN
                              rows.forEach((row) => { var RowTime = Date.parse(row['Timestamp']); 
                                                      if ((DataTime - RowTime) < obsts*3600000 ) {
                                                         var phi   = Math.PI/180 * parseFloat(row['el']);
                                      		         var theta = Math.PI/180 * parseFloat(row['az']); 
          		                                 var x = -r * Math.cos(theta) * Math.cos(phi);
          		                                 var y = r * Math.sin(theta) * Math.cos(phi);
                                                         var z = r * Math.sin(phi);
                                                         var s = 1 + 9 * Math.cos(phi);
                                                         ts.push(row['Timestamp']);
                                                         va.push(x);
                                                         vb.push(y);
                                                         vc.push(z);
                                                         vd.push(row['cno']);
                                                         ve.push("<b>PRN"+row['PRN'] +"</b><br>Azimuth: " + row['az'] + "°<br>Elevation: " + row['el'] + "°<br>C/NO: "+ row['cno']+"dB");
                                                         //vf.push(parseInt(s));
                                                      }});
                              // console.log({ ts, va, vb, vc, vd, ve });
                              return { ts, va, vb, vc, vd, ve }; } 
      const { ts, va, vb, vc, vd, ve } = unpack(rows); 
      // console.log({ ts, va, vb, vc, vd, ve });
      var data = [{ x: va,
                    y: vb,
                    z: vc,
                    hoverinfo: 'text', hovertext: ve,
                    type: 'scatter3d',
                    mode: 'markers',
                    marker: {size: 2, color: vd, colorscale: 'solar', showscale: true, reversescale: false, opacity: 0.3 }
                    } ]; // colorscale = c('#FFE1A1', '#683531'), Viridis

      var layout = {type: 'scatter3d', height: 600, margin: {l: 50, t: 60, b:30 },

                    scene: { aspectmode: 'manual', aspectratio: {x: 1, y:1, z: 0.5 }, // cube+data = verzerrt
                             /* camera: { up: {x:0 , y:0, z:1},
                                       center: {x: 0, y:0, z: 1},
                                       eye: {x: 0.001, y: 0, z: 1.5} }, */
                             xaxis: { title: 'EW', range: [-1,+1], showticklabels: false, gridcolor: 'white', zerolinecolor: 'white' }, 
                             yaxis: { title: 'N<br>S', range: [-1,+1], showticklabels: false, gridcolor: 'white', zerolinecolor: 'white'  }, 
                             zaxis: { title: { font: { color: 'white' }}, range: [0,+1], showticklabels: false, 
                                      backgroundcolor: 'rgb(230, 230,200)', gridcolor: 'white',
                                      showbackground: true, zerolinecolor: 'white' } },
                             title: {text: 'Satellite Visibility ('+obsts+' hours; n= ' + va.length + ')',
                             font: { size: 20, color: 'blue' }, xref: 'container', x: 0.0, xanchor: 'left', yref: 'container', y: 0.975, yanchor: 'top' }
                    }
    
      Plotly.react('Chart4', data, layout); 
      // Enable Tab if data available
      $('a[href="#skyplott"]').html('<span class="" role="status" aria-hidden="false"><?php echo _("Skyplot"); ?></span>').removeClass('disabled');    
    });

    // NOTE Satellite ephemeris observations

    var r=1;
    Plotly.d3.csv('strobs.csv', function(err, rows) { 
      function unpack(rows) { var DataTime = Date.parse(rows[rows.length-1]['Timestamp']);
                              const ts=[], va=[], vb=[], vc=[], vd=[], ve=[], vf=[];  // Timestamp,az,el,cno,PRN
                              rows.forEach((row) => { var RowTime = Date.parse(row['Timestamp']); 
                                                      if ((DataTime - RowTime) < obsts*3600000 ) {
                                                         var phi   = row['el']; // Math.PI/180 * parseFloat(row['el']);
                                      		         var theta = row['az']; // Math.PI/180 * parseFloat(row['az']); 
                                                         ts.push(row['Timestamp']);
                                                         va.push(phi);
                                                         vb.push(theta);
                                                         vd.push(row['cno']);
                                                         vc.push("<b>PRN"+row['PRN'] +"</b><br>Azimuth: " + row['az'] + "°<br>Elevation: " + row['el'] + "°<br>C/NO: "+ row['cno']+"dB");
                                                      }});
                              return { ts, va, vb, vc, vd }; } 
      const { ts, va, vb, vc, vd } = unpack(rows); 
      var data = [{ theta: vb,
                    r: va,
                    hoverinfo: 'text', hovertext: vc,
                    type: 'scatterpolar',
                    mode: 'markers',
                    marker: {size: 4, color: vd, colorscale: 'solar', showscale: true, reversescale: false, opacity: 0.3 }
                    } ]; // colorscale = c('#FFE1A1', '#683531'), Viridis

      var layout = {height: 400, margin: {l: 50, t: 60, b:30 },
                    polar: { radialaxis: {angle: 90, orientation: 90, range: [90, 0] },
                             angularaxis: { rotation: 90, direction: 'clockwise', dtick: 30} },
                             title: {text: 'Satellite Visibility ('+obsts+' hours; n= ' + va.length + ')',
                             font: { size: 20, color: 'blue' }, xref: 'container', x: 0.0, xanchor: 'left', yref: 'container', y: 0.975, yanchor: 'top' }
                    }
    
      Plotly.react('Chart5', data, layout); 
      // Enable Tab if data available
      $('a[href="#skyplott"]').html('<span class="" role="status" aria-hidden="false"><?php echo _("Skyplot"); ?></span>').removeClass('disabled');    
    });

    // NOTE WORKING Two Parameter Live Chart, limited to 20 minutes, adjustable
    // Encounters errors, to be checked
    var timespan=30;
    Plotly.d3.csv('rtksvr.csv', function(err, rows) {
      function unpack(rows) { var DataTime = Date.parse(rows[rows.length-1]['Timestamp']);
                              const ts=[], va=[], vb=[];
                              rows.forEach((row) => { var RowTime = Date.parse(row['Timestamp']); 
                                                      function round(number, decimals) { return +(Math.round(number + "e+" + decimals)  + "e-" + decimals); }
                                                      if ((DataTime - RowTime) < timespan*3600000/60 ) {
                                                         ts.push(row['Timestamp']);
                                                         va.push(row['R95']);
                                                         vb.push(row['RATIO']);
                                                      }});
                              return { ts, va, vb }; } 

      const { ts, va, vb } = unpack(rows);
      var data = [{ x: ts,
                    y: va,
                    name: 'R95',
                    texttemplate: "Price: %{R95:$.2f}"  },
                  { x: ts,
                    y: vb,
                    name: 'A-Ratio' } ];
      var layout = {autosize: true, height: 250, margin: {l: 50, t: 60, b:30 },
                    title: {text: 'Live: Ratio factor of ambiguity validation and R95',
                    font: { size: 20, color: 'blue' }, xref: 'container', x: 0.0, xanchor: 'left', yref: 'container', y: 0.975, yanchor: 'top' },
                    xaxis: { type: 'date', tickformat: '%X' },
                    yaxis: { type: 'linear', zeroline: false, rangemode: 'tozero' },
                    yaxis: { title: 'A-Ratio', type: 'log', zeroline: true, side: 'left', rangemode: 'tozero', overlaying: 'y' }
                    };
      Plotly.react('Chart3', data, layout); 
      // Enable Tab if data available + show live view ...
      $('a[href="#metricss"]').html('<span class="" role="status" aria-hidden="false"><?php echo _("Live"); ?></span>').removeClass('disabled');    
      $('a[href="#metricss"]').tab('show');
    });

    var UpdRate = 2000;
    var cnt = 0;
    var meas = [];
    var interval = setInterval(function() {
      ++cnt;
      var elem = document.getElementById("pgb");
      elem.innerHTML = Math.min(100*cnt/20,100).toFixed(0) + '%';
      elem.style.width = Math.min(cnt/20*100,100) + '%'; 
      Plotly.d3.csv('rtksvr.csv', function(data) { processData(data) } );
      function processData(allRows) {
            var last   = allRows.length-1;
            var R95    = allRows[last]['R95'];
            var RATIO   = allRows[last]['RATIO']; 
            console.log("RATIO="+RATIO);
            if (parseFloat(R95) < 1.5 && parseFloat(RATIO) > 1 && cnt*UpdRate/1000 > 40 ) { // Mind.Anf.gem.Durchführungshinweise LGL BW, 10 Sekunden Stab+30s Messung
              $('#btn-meas').html('<span class="" role="status" aria-hidden="false"><?php echo _("Start Measurement"); ?></span>').prop('disabled', false);  					//removeClass('disabled');    
              meas = allRows.slice(allRows.length - 30, allRows.length);
            } else {
              $('#btn-meas').html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span><?php echo _("Stabilizing ..."); ?>').prop('disabled', true)     //addClass('disabled');    
            }
            var time   = new Date(Date.parse(allRows[last]['Timestamp'])); 
            var update = { x: [[time],[time]], y: [[R95],[RATIO]] }
            var olderTime  = time.setMinutes(time.getMinutes() - timespan);
            var futureTime = time.setMinutes(time.getMinutes() + timespan);
            var minuteView = { xaxis: { range: [olderTime,futureTime], type: 'date', tickformat: '%X'} }
            Plotly.relayout('Chart3', minuteView);
            Plotly.extendTraces('Chart3', update, [0,1]);
            if(++cnt === 150) clearInterval(interval) // nach 1500x ist schluss
        }
    } , UpdRate);

    // NOTE Der Mess-Knopf ...
    $("#btn-meas").click(function() {
//      document.getElementById("measres").value = "hallo";
/*      var elem = document.getElementById("pgb");
      for (i = 0; i <= 100; i++) { elem.text = i + '%';
                                   elem.style.width = i + '%'; } */
        console.log(meas.length); 
        console.log(meas[0]['Timestamp']);

        var PArr = meas.map(function(ae) { return ae['PDOP']; });
        var SArr = meas.map(function(ae) { return ae['NS']; });
        var QArr = meas.map(function(ae) { return (ae['Q'] == 1) ? 100 : 0; });
        const RQ = QArr.reduce((a,b) => a + b, 0) / meas.length;
        var MR = 0;
        var MH = 0;
        var MA = 0;
        for (i=0; i<meas.length; i++) {
            MR += parseFloat(meas[i]['UTMR']);
            MH += parseFloat(meas[i]['UTMH']);
            MA += parseFloat(meas[i]['ALT']);
        }
        MR = MR / meas.length;
        MH = MH / meas.length;
        MA = MA / meas.length;

        var out = "Timestamp|PDOP|SAT|RTK|UTM-Zone|UTM-Right|UTM-Height|Altitude";
        var out = out + "\r\n" + meas[0]['Timestamp'] + '|'+ Math.min(...PArr).toFixed(4) +'-'+ Math.max(...PArr).toFixed(4) +'|';
        var out = out + Math.min(...SArr).toFixed(0) +'-'+ Math.max(...SArr).toFixed(0) +'|'+RQ.toFixed(1)+'%|';
        var out = out + meas[0]['UTMZ']+'|'+ MR.toFixed(3) +'|'+ MH.toFixed(3) +'|'+ MA.toFixed(3) +'|'; 
        document.getElementById("measres").value = out;
        $('#btn-meas').html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span><?php echo _("Stabilizing ..."); ?>').prop('disabled', true)
        cnt=0;
    });
    


    <!--/script-->
