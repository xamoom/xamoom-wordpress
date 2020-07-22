(function ($) {
	'use strict';

	/**
	 * Nothing to see here.
	 */

})(jQuery);

var language = null;
class tourMap {
	get location() {
		return this.locationValue;
	};

	set location(loc) {
		this.locationValue = loc;
	};

	get bounds() {
		return this.boundsValue;
	};

	set bounds(bounds) {
		this.boundsValue = bounds;
	};

	get map() {
		return this.mMap;
	};

	set map(map) {
		this.mMap = map;
	};

	get selectorId() {
		return this.selectorIdvalue;
	};

	set selectorId(id) {
		this.selectorIdvalue = id;
	};

	get scaleX() {
		return this.scaleXValue;
	}

	set scaleX(scaleX) {
		this.scaleXValue = scaleX;
	}

	get tourData() {
		return this.tourDataObject;
	}

	set tourData(tourData) {
		this.tourDataObject = tourData;
	}
	constructor(id, mapId, geojsonFile, scaleX, map, lang, bounds) {
		language = lang;
		this.map = map;
		this.selectorId = `${id}tour-${mapId}`;
		this.scaleX = scaleX;
		this.bounds = bounds;
		this.tourData = {
			length: null,
			gain: null,
			loss: null,
			name: null,
			unit: 'metric',
		};

		this.initEventListeners(map, id, mapId,);

		this._displayGEOJSON(geojsonFile, scaleX);

		this._insertLocalizedValues();

		this._initUnitSwitch();
	}

	/**
	 * sets up event listeners for popup features
	 */
	initEventListeners(map, id, mapId) {
		const self = this;
		const info = document.querySelector(`#xamoom-map-${this.selectorId} > button.info`);
		if (info) {
			const tourinfo = document.querySelector(`#xamoom-map-${this.selectorId} > .tour-info`);

			if (!!('ontouchstart' in window)) { //check for touch device
				info.addEventListener('click', function (e) {
					if (tourinfo.style.display === "none") {
						tourinfo.style.display = "block";
					} else {
						tourinfo.style.display = "none";
					}
				});
			}
			else {
				info.addEventListener('mouseover', function (e) {
					tourinfo.style.display = "block";
				});
				info.addEventListener('mouseout', function (e) {
					tourinfo.style.display = "none";
				});
			}
			document.querySelector(`#xamoom-map-${this.selectorId} > .expand`).addEventListener('click', function (e) {
				map.flyToBounds(self.bounds);
				e.stopPropagation();
			});
		}
	};


	_fitToBounds(map) {
		// let options = { padding: `${this.scaleX}` };
		// options = {};
		// const container = this.map.getContainer();
		// const heightPadding = (container.clientHeight * ((100 - this.scaleX) / 100) ) / 2; 
		// const widthPadding = (container.clientWidth * ((100 - this.scaleX) / 100) ) / 2; 
		// options = { padding: { top: heightPadding, bottom: heightPadding, left: widthPadding, right: widthPadding } };
		const bounds = this.pad(this.bounds, ((100 - this.scaleX) / 100));
		map.fitBounds(bounds);
	};

	// @method pad(bufferRatio: Number): LatLngBounds
	// Returns bounds created by extending or retracting the current bounds by a given ratio in each direction.
	// For example, a ratio of 0.5 extends the bounds by 50% in each direction.
	// Negative values will retract the bounds.
	pad(bounds, bufferRatio) {
		var sw = bounds._sw,
			ne = bounds._ne,
			heightBuffer = Math.abs(sw.lat - ne.lat) * bufferRatio,
			widthBuffer = Math.abs(sw.lng - ne.lng) * bufferRatio;

		return new mapboxgl.LngLatBounds(
			[sw.lng - widthBuffer, sw.lat - heightBuffer],
			[ne.lng + widthBuffer, ne.lat + heightBuffer]);
	};

	_displayGEOJSON(fileUrl, scaleX) {
		const self = this;

		fetch(fileUrl)
			.then(response => response.json())
			.then((geojsonRaw) => {
				this._showGEOJSON(geojsonRaw, this.map, scaleX);
			})
			.catch((err) => {
				console.log(err);
			})
	};
	_showGEOJSON(geojson, map, scaleX) {
		const geoJSONData = this._removeUnnecesarryDataFromGEOJSON(geojson);
		const geojsonlayer = L.geoJSON(geoJSONData);
		geojsonlayer.addTo(map);
		// set(this, 'ogBounds', geojsonlayer.getBounds());
		const bounds = geojsonlayer.getBounds();
		map.fitBounds(bounds.extend(this.bounds).pad(((100 - scaleX) / 100)));
		this.bounds = bounds;
		this._generateTourData(geojson);
	};
	_removeUnnecesarryDataFromGEOJSON(geojson) {
		// remove spots
		try {
			for (let i = geojson.features.length - 1; i >= 0; i--) {
				if (geojson.features[i].geometry.type === 'Point') {
					geojson.features.splice(i, 1);
				}
			}
		} catch (error) {
			console.error(error);
		}

		return geojson;
	};

	_calculateLength(linestring) {
		const length = turf.length(linestring);
		const rounded = Math.round((length + Number.EPSILON) * 100) / 100;
		return rounded;
	};

	_smoothElevation(coordinates) {
		const dataLength = coordinates.length;
		if (dataLength === 0) {
			return;
		}

		const smoothingSize = 2;
		const toSmooth = coordinates;
		let newElevations = [];
		for (let i = 0; i < dataLength; i++) {
			let sumValues = 0;
			let start = i - smoothingSize;
			if (start < 0) {
				start = 0;
			}
			let end = i + smoothingSize;
			if (end > dataLength - 1) {
				end = dataLength - 1;
			}
			for (let j = start; j <= end; j++) {
				sumValues += toSmooth[j][2];
			}
			newElevations.push(Math.floor(sumValues / (end - start + 1)));
		}

		return newElevations;
	};
	_calculateElevation(elevationData) {
		let gainTotal = 0;
		let lossTotal = 0;
		// first calculate from raw data, then smooth out
		// how to calculate gain and loss:
		// first coord is starting point. then you compare it with the next point.
		// if the point is larger, add the difference to gain, if it is smaller, add the difference to loss
		for (let i = 0; i < elevationData.length - 1; i++) {
			const current = elevationData[i];
			const next = elevationData[i + 1];

			if (next > current) {
				gainTotal += (next - current);
			}
			else if (current > next) {
				lossTotal += current - next;
			}
		}
		return [Math.round(gainTotal), Math.round(lossTotal)];
	};

	_insertLocalizedValues() {
		// document.querySelector(`#xamoom-map-${this.selectorId} > div.tour-info > div > div > div:nth-child(3) > div.tour-meta-label.tour-meta-label-time`).innerText += '5 km/h';
	};

	_createChart(elevationData, tracklengthInKm) {
		const chart = document.querySelector(`#elevationChart${this.selectorId}-metric`);
		if (!chart) {
			return;
		}
		const ctx_metric = chart.getContext('2d');
		const totalLengthInMeter = length * 1000;
		const trackLengthInMiles = Math.round(((tracklengthInKm / 1.609344) + Number.EPSILON) * 100) / 100;
		let elevationDataInFeet = [];
		const self = this;
		const onePoint = tracklengthInKm / elevationData.length;
		const onePointImperial = trackLengthInMiles / elevationData.length;
		let xPoints = [];
		let xPoints_imperial = [];
		for (let i = 0; i < elevationData.length; i++) {
			xPoints[i] = onePoint + (onePoint * i);
			xPoints_imperial[i] = onePointImperial + (onePointImperial * i);
			elevationDataInFeet.push(Math.round(elevationData[i] * 3.281));
		}
		let elevationLabel = 'Elevation';
		if (language === 'de') {
			elevationLabel = 'Höhe';
		}
		const options = {
			legend: {
				display: false,
			},
			tooltips: {
				callbacks: {
					title(tooltipItem) {
						let unit = 'km';
						let length = tooltipItem[0].xLabel;
						if (self.tourData.unit === 'imperial') {
							unit = 'mi';
						}
						return `${length.toFixed(2)}${unit}`;
					},
					label(tooltipItem) {
						let unit = 'm';
						let elevation = tooltipItem.yLabel;
						if (self.tourData.unit === 'imperial') {
							unit = 'ft';
						}
						return `${elevationLabel}: ${elevation}${unit}`;
					},
				},
			},
			scales: {
				xAxes: [{
					scaleLabel: {
						display: true,
					},
					ticks: {
						beginAtZero: true,
						stepSize: 5,
						maxTicksLimit: 18,
						callback: function (value, index, values) {
							return Math.floor(value);
						}
					}
				}],
				yAxes: [{
					scaleLabel: {
					},
					ticks: {
						maxTicksLimit: 5,
					}
				}]
			}
		};
		new Chart(ctx_metric, {
			type: 'line',
			data: {
				datasets: [{
					label: elevationLabel,
					backgroundColor: 'rgba(25, 182, 237, 0.5)',
					fill: true,
					data: elevationData,
				}],
				labels: xPoints,
			},
			options
		});

		const ctx_imperial = document.querySelector(`#elevationChart${this.selectorId}-imperial`).getContext('2d');

		new Chart(ctx_imperial, {
			type: 'line',
			data: {
				datasets: [{
					label: elevationLabel,
					backgroundColor: 'rgba(25, 182, 237, 0.5)',
					fill: true,
					data: elevationDataInFeet,
				}],
				labels: xPoints_imperial,
			},
			options
		});
	};
	_generateTourData(geojson) {
		let length = 'N/A';
		let name = 'N/A';
		let gain = '---';
		let loss = '---';

		try {
			length = this._calculateLength(geojson.features[0]);
			this.tourData.length = length;
		} catch (error) {
			console.error(error);
		}
		try {
			name = geojson.features[0].properties.name;
			this.tourData.name = name;
		} catch (error) {
			console.error(error);
		}
		try {
			const elevationData = this._smoothElevation(geojson.features[0].geometry.coordinates);
			const elevation = this._calculateElevation(elevationData);
			if (parseFloat(length, 10) && elevationData) {
				this._createChart(elevationData, length);
			}
			gain = elevation[0];
			loss = elevation[1];
			this.tourData.gain = gain;
			this.tourData.loss = loss;
		} catch (error) {
			console.log(error);
		}
		this._switchUnit(this.tourData.unit);
	};

	_insertTourData(tourData) {
		let meanTime = "Ø duration at 5 km/h";
		let lengthUnit = 'km';
		let heightUnit = 'hm';
		let time = (tourData.length / 5);
		let length = tourData.length.toString().replace('.', ',')
		if (language === 'de') {
			meanTime = "Ø Dauer bei 5 km/h";
		}
		if (tourData.unit === 'imperial') {
			meanTime = "Ø duration at 3.1 mph";
			lengthUnit = 'mi';
			heightUnit = 'ft';
			time = (tourData.length / 3.10686);
			length = tourData.length;
			if (language === 'de') {
				meanTime = "Ø Dauer bei 3,1 mph";
			}
		}
		const html = document.querySelector(`#xamoom-map-${this.selectorId} > div.tour-info`);
		html.querySelector("div.box-content > div > div:nth-child(1) > div.tour-stats.tour-stats-big > span:nth-child(2)").innerHTML = length;
		html.querySelector("span").innerHTML = tourData.name;
		html.querySelector("div > div > div:nth-child(2) > div:nth-child(1) > span:nth-child(2)").innerHTML = tourData.gain;
		html.querySelector("div > div > div:nth-child(2) > div:nth-child(2) > span:nth-child(2)").innerHTML = tourData.loss;
		let hour = time.toString().split('.')[0];
		let minutes = Math.floor(((time - parseInt(hour)) * 60));

		if (parseInt(hour, 10) < 10) {
			hour = "0" + hour;
		}
		if (parseInt(minutes, 10) < 10) {
			minutes = "0" + minutes;
		}
		html.querySelector("div > div > div:nth-child(3) > div.tour-stats.tour-stats-time.tour-stats-big > strong").innerHTML = `${hour}:${minutes}`;
		html.querySelector("div > div > div:nth-child(1) > div.tour-stats.tour-stats-big.tour-stats-length > span.tour-stats-unit").innerText = lengthUnit;
		html.querySelector("div > div > div:nth-child(2) > div:nth-child(1) > span.tour-stats-unit").innerText = heightUnit;
		html.querySelector("div > div > div:nth-child(2) > div:nth-child(2) > span.tour-stats-unit").innerText = heightUnit;
		html.querySelector("div > div > div:nth-child(3) > div.tour-meta-label.tour-meta-label-time").innerText = meanTime;
	};

	_initUnitSwitch() {
		const self = this;
		const imperial = jQuery(`#xamoom-map-${this.selectorId} > div.unitSwitch-wrap.cf > div > a[data-unit-type="imperial"]`);
		const metric = jQuery(`#xamoom-map-${this.selectorId} > div.unitSwitch-wrap.cf > div > a[data-unit-type="metric"]`);
		const chart_imperial = document.querySelector(`#elevationChart${this.selectorId}-imperial`);
		const chart_metric = document.querySelector(`#elevationChart${this.selectorId}-metric`);
		if (language === 'en') {
			imperial.addClass('active');
			this.tourData.unit = 'imperial';
			if (chart_imperial) {
				chart_imperial.style.display = 'block';
			}
		} else {
			metric.addClass('active');
			this.tourData.unit = 'metric';
			if (chart_metric) {
				chart_metric.style.display = 'block';
			}
		}
		imperial.click(function () {
			imperial.addClass('active');
			metric.removeClass('active');
			self._switchUnit('imperial');
			if (chart_metric) {
				chart_metric.style.display = 'none';
				chart_imperial.style.display = 'block';
			}
		});
		metric.click(function () {
			metric.addClass('active');
			imperial.removeClass('active');
			self._switchUnit('metric');
			if (chart_imperial) {
				chart_metric.style.display = 'block';
				chart_imperial.style.display = 'none';
			}
		});
	};

	_switchUnit(unit) {
		let tourData = this.tourData;
		if (unit === 'imperial') {
			this.tourData.unit = 'imperial';
			tourData = {
				length: Math.round(((this.tourData.length / 1.609344) + Number.EPSILON) * 100) / 100,
				gain: Math.round(this.tourData.gain * 3.281),
				loss: Math.round(this.tourData.loss * 3.281),
				name: this.tourData.name,
				unit: 'imperial'
			}
		} else {
			tourData.unit = 'metric';
			this.tourData.unit = 'metric';
		}
		this._insertTourData(tourData);
		// calculate using this.tourData
		// values in this.tourData are metric
		// if metric, use this.tourData raw, else convert 
		// also replace labels with hm to feet, 5kmh to 3mph, km to miles
	};

}
