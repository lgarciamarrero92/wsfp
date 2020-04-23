import {expose} from 'threads'
import {Observable} from 'observable-fns'
import {Subject} from 'threads/observable'
import * as turf from '@turf/turf'
const GeoTIFF = require('geotiff')

const images = {}
var partition = {}
let subject = new Subject()

const gisFileUrl = {
    iec1: 'CUB_capacity-factor_IEC1.tif',
    iec2: 'CUB_capacity-factor_IEC2.tif',
    iec3: 'CUB_capacity-factor_IEC3.tif',
    pvout: 'Cuba_GISdata_LTAy_YearlyMonthlyTotals_GlobalSolarAtlas-v2_GEOTIFF/PVOUT.tif'
}
const obj = {
    progress(){
        return Observable.from(subject) 
    },
    async get(data){
        let model = {}
        for(let i = 0 ; i < data.length ; i++ ){
            let polygon = JSON.parse(data[i]['zone']['feature'])
            let zone = data[i]['zone']
            model[zone['id']] = {'none':{
                'zone': zone.name,
                'energy': 0,
                'costs': 0,
                'solarArea': 0,
                'windArea': 0
            }}
            if(zone['type'] == 'eolic'){
                for(let j = 0 ; j < data[i]['wind_turbines'].length; j++){
                    let tec = data[i]['wind_turbines'][j]
                    let gis = null
                    if(tec.iec_class == 1){
                        gis = gisFileUrl.iec1
                    }else if(tec.iec_class == 2){
                        gis = gisFileUrl.iec2
                    }else{
                        gis = gisFileUrl.iec3
                    }
                    if( !partition[gis] ){
                        partition[gis] = await obj.gridZoneGISValue(polygon,gis)
                    }
                    const rs = obj.getWindCoefficients(polygon,tec,partition[gis])
                    rs.zone = zone.name
                    model[zone.id][tec.model] = rs
                }
            }
            if(zone['type'] == 'solar'){
                for(let j = 0 ; j < data[i]['solar_panels'].length; j++){
                    let tec = data[i]['solar_panels'][j]
                    let gis = gisFileUrl.pvout
                    if( !partition[gis] ){
                        partition[gis] = await obj.gridZoneGISValue(polygon,gis)
                    }
                    const rs = obj.getSolarCoefficients(polygon,tec,partition[gis])
                    rs.zone = zone.name
                    model[zone.id][tec.model] = rs
                }
            }
            partition = {}
            subject.next((i+1)/data.length)
        }
        return model
    },
    getSolarCoefficients(polygon,tec,points){

        var bbox = turf.bbox(polygon);
        var area = turf.area(polygon)
        var options = {units: 'meters',mask: polygon};
        
        //Calculate mean solar potential
        var mean = 0
        for( var i = 0 ; i < points.length ; i++ ){
            mean += points[i].value
        }
        mean /= (points.length)
        
        //Calculate solar panels distribution
        let w = tec.width
        let h = tec.height
        var grid = turf.pointGrid(bbox, h*1.6*2 , options);
        var rows = []
        rows.push(grid.features[0])
        for( var i = 1 ; i < grid.features.length; i++ ){
            var p = grid.features[i].geometry.coordinates
            var belongs = true
            for(var j = 0 ; j < rows.length ; j++ ){
                if(p[1] == rows[j].geometry.coordinates[1]){
                    belongs = false;
                    break
                }
            }
            if(belongs){
                rows.push(grid.features[i])
            }
        }
        var bboxpoly = turf.bboxPolygon(bbox);
        var numberOfPanels = 0
        for( var i = 0 ; i < rows.length; i++ ){
            var p = rows[i]
            while( turf.booleanPointInPolygon(p, bboxpoly) ){
                if(turf.booleanPointInPolygon(p, polygon)){
                    rows[i] = p
                }
                var distance = w/1000.0; //Kilometers
                var bearing = -90;
                p = turf.destination(p, distance, bearing);
            }
        }
    
        let features = []
        for( var i = 0 ; i < rows.length; i++ ){
            var numberInRow = 0
            var p = rows[i]
            var fp = p
            var lp = p
            while( turf.booleanPointInPolygon(p, bboxpoly) ){
                if(turf.booleanPointInPolygon(p, polygon)){
                    lp = p
                    numberInRow += 2
                    if(fp == -1 ){
                        fp = p
                    }
                }else{
                    if( lp != fp ){
                        var cr = turf.destination(lp, h*1.88/1000.0, 0);
                        var cl = turf.destination(fp, h*1.88/1000.0, 0);
                        var points = turf.featureCollection([fp,lp,cl,cr]);
                        var bbPolygon = turf.convex(points);
                        bbPolygon.properties.popupContent = numberInRow+2 + ' panels'
                        bbPolygon.properties.style = {
                            "color": "black",
                            "opacity": 0.9,
                            "weight": 1
                        }
                        features.push( bbPolygon )
                        /*
                        distribution.addLayer(L.geoJSON(bbPolygon,{
                            style: {
                                "color": "black",
                                "opacity": 0.9,
                                "weight": 1
                            }
                        }).bindPopup(numberInRow+2 + ' panels'));
                        */
                        numberOfPanels += (numberInRow+2)
                        numberInRow = 0;
                    }
                    fp = -1;
                    lp = -1;
                }
                var distance = w/1000.0; //Kilometers
                var bearing = 90;
                p = turf.destination(p, distance, bearing);
            }
            if( lp != fp ){
                var cr = turf.destination(lp, h*1.88/1000.0, 0);
                var cl = turf.destination(fp, h*1.88/1000.0, 0);
                var points = turf.featureCollection([fp,lp,cl,cr]);
                var bbPolygon = turf.convex(points);
                bbPolygon.properties.popupContent = numberInRow+2 + ' panels'
                bbPolygon.properties.style = {
                    "color": "black",
                    "opacity": 0.9,
                    "weight": 1
                }
                features.push( bbPolygon )
                /*
                distribution.addLayer(L.geoJSON(bbPolygon,{
                    style: {
                        "color": "black",
                        "opacity": 0.9,
                        "weight": 1
                    }
                }).bindPopup(numberInRow+2 + ' panels'));
                */
                numberOfPanels += (numberInRow+2)
                numberInRow = 0;
            }
        }
        var distribution = turf.featureCollection(features)
        let results = {
            'type': 'solar',
            'model': tec.model,
            'number': numberOfPanels,
            'energy': (numberOfPanels*tec.nominal_power*mean)/1000.0,
            'costs': (tec.invest_cost*numberOfPanels + tec.om_cost_per_year*20.0*numberOfPanels)/20.0,
            'distribution': JSON.stringify(distribution),
            'solarArea': area,
            'windArea': 0.0
        }
        return results;
    },
    getWindCoefficients(polygon,tec,points){
        //var polygon = this.$drawnItems._layers[item].toGeoJSON();
        var area = turf.area(polygon)
        var mind = (5.0*tec.rotor_diameter)/1000
    
        points.sort((a,b)=>{
            return (b.value - a.value)
        })
        var locations = []
        var sum = points[0].value
        locations.push(points[0].point)
        for( var i = 1 ; i < points.length ; i++ ){
            var p = points[i].point
            var n = locations.length;
            var belongs = true
            for( var j = n-1 ; j >= 0 ; j-- ){
                if(turf.distance(p, locations[j]) <= mind ){
                    belongs = false;
                    break;
                }
            }
            if(belongs == true ){
                locations.push(p);
                sum += points[i].value
            }
        }
        
        var distribution = turf.featureCollection(locations);
    
        let results = {
            'type': 'eolic',
            'model': tec.model,
            'number': locations.length,
            'energy': sum*tec.nominal_power*8760.0,
            'costs': (tec.invest_cost*locations.length + tec.om_cost_per_year*20.0*locations.length)/20.0,
            'distribution': JSON.stringify(distribution),
            'solarArea': 0.0,
            'windArea': area,
            'capacity_factor': sum/locations.length
        }
        return results
    },
    async gridZoneGISValue(polygon,gisFileUrl){
        
        if( !images[gisFileUrl]){
            var tiff = await GeoTIFF.fromUrl(gisFileUrl /*,{headers: {'Cache-Control' : 'public, max-age=604800, inmutable'}}*/)
            images[gisFileUrl] = await tiff.getImage()
        }
    
        var image = images[gisFileUrl]
        var bbox = turf.bbox(polygon);
        var area = turf.area(polygon);
    
        var d = 1 //Grid length in meters
        if(area > 10000){ //No more than 1000 points in a grid
            d = Math.sqrt(area)/100.0
        }
        /*
            Make a grid with length d in this zone
        */
        var options = {units: 'meters',mask: polygon};
        var grid = turf.pointGrid(bbox, d , options);
        /*
            Calculate bbox in pixel coordinates
        */
        bbox = [
            Math.min(bbox[0],bbox[2]),
            Math.max(bbox[1],bbox[3]),
            Math.max(bbox[0],bbox[2]),
            Math.min(bbox[1],bbox[3]),
        ]
        const [oX, oY] = image.getOrigin();
        const [imageResX, imageResY] = image.getResolution();
        let wnd = [
            Math.floor((bbox[0] - oX) / imageResX),
            Math.floor((bbox[1] - oY) / imageResY),
            Math.ceil((bbox[2] - oX) / imageResX),
            Math.ceil((bbox[3] - oY) / imageResY),
        ];
        /*
            Read Gis Data in that window
        */
        const data = await image.readRasters({
            window: wnd,
        });
        /*
            Iterate grid points and fill with gis value
        */
        var points = []
        for( var i = 0 ; i < grid.features.length ; i++ ){
            
            var p = grid.features[i].geometry.coordinates
            
            let pc = [
                Math.floor((p[0] - oX) / imageResX)-wnd[0],
                Math.floor((p[1] - oY) / imageResY)-wnd[1]
            ]
    
            let ind = pc[1]*data.width + pc[0]
            let val = data[0][ind]
    
            points.push({
                'point': grid.features[i],
                'value' : val
            })
        }
        return points;
    }
}
expose(obj)
