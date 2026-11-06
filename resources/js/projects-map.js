// Projects map: OpenFreeMap vector tiles (MapLibre GL) in 3D, each project shown by its photo.
// Overlapping projects group into one photo marker (no number); tapping a group zooms in. Loaded on demand from app.js.
import { Map, Marker, Popup, NavigationControl, AttributionControl, LngLatBounds, setWorkerUrl } from 'maplibre-gl';
import workerUrl from 'maplibre-gl/dist/maplibre-gl-worker.mjs?worker&url';
import 'maplibre-gl/dist/maplibre-gl.css';

setWorkerUrl(workerUrl);

// Default base map; the page can name another (config site.map_style / MAP_STYLE_URL) if this service is down (S2)
const STYLE = 'https://tiles.openfreemap.org/styles/liberty'; // includes 3D buildings from zoom 14
const PAKISTAN = { center: [69.3451, 30.3753], zoom: 4.6 };
// Flat, seen from above like Google Maps, so markers sit where the buildings are drawn. Visitors can still tilt
// the map into 3D with the controls (or two fingers / right-drag).
const PITCH = 0;
const MAX_ZOOM_IN = 16; // street level: tapping a group never zooms further than this

const escape = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

function card(p) {
    return `
        <a href="${escape(p.url)}" class="mp-card">
            ${p.image ? `<img src="${escape(p.image)}" alt="" loading="lazy">` : ''}
            <span class="mp-card-body">
                ${p.stage ? `<span class="mp-card-stage">${escape(p.stage)}</span>` : ''}
                <strong>${escape(p.name)}</strong>
                <span class="mp-card-meta">
                    ${p.logo ? `<img src="${escape(p.logo)}" alt="">` : ''}
                    ${escape(p.developer ?? '')}
                </span>
                <span class="mp-card-loc">${escape(p.location)}</span>
                <span class="mp-card-cta">View project →</span>
            </span>
        </a>`;
}

/** Projects that share one spot: a short list of mini rows. */
function list(projects) {
    return `<ul class="mp-list">${projects.map((p) => `
        <li><a href="${escape(p.url)}">
            ${p.image ? `<img src="${escape(p.image)}" alt="">` : '<span></span>'}
            <span><strong>${escape(p.name)}</strong><small>${escape(p.location)}</small></span>
        </a></li>`).join('')}</ul>`;
}

/**
 * A pin: the round photo sits above a point whose tip is exactly on the project's coordinates (the marker is anchored
 * at its bottom), like a Google Maps pin. Groups get a stacked look instead of a number.
 */
function markerElement(image, label, group) {
    const el = document.createElement('button');
    el.type = 'button';
    el.className = group ? 'mp-pin mp-pin--group' : 'mp-pin';
    el.setAttribute('aria-label', label);
    el.innerHTML = `<span class="mp-photo">${image ? `<img src="${escape(image)}" alt="">` : '<span></span>'}</span><span class="mp-tip" aria-hidden="true"></span>`;
    return el;
}

/** Labels in English where the map data has an English name, otherwise its Latin spelling; places with neither stay unlabelled. */
function englishLabels(map) {
    const name = ['coalesce', ['get', 'name:en'], ['get', 'name_en'], ['get', 'name:latin'], ''];
    map.getStyle().layers.forEach((layer) => {
        const field = layer.type === 'symbol' && map.getLayoutProperty(layer.id, 'text-field');
        if (field && JSON.stringify(field).includes('name')) {
            map.setLayoutProperty(layer.id, 'text-field', name);
        }
    });
}

/** Opening view: the city with the most projects (Karachi today); other cities appear when zooming out. */
function openingBounds(points) {
    const byCity = {};
    points.forEach((p) => (byCity[p.city] ??= []).push(p));
    const busiest = Object.values(byCity).sort((a, b) => b.length - a.length)[0];
    const bounds = new LngLatBounds();
    busiest.forEach((p) => bounds.extend([p.lng, p.lat]));
    return bounds;
}

const features = (points, keep) => ({
    type: 'FeatureCollection',
    features: points.flatMap((p, i) => (keep && !keep.has(p.slug) ? [] : [{ type: 'Feature', id: i, properties: { i, image: p.image }, geometry: { type: 'Point', coordinates: [p.lng, p.lat] } }])),
});

/** onFail: called when the map style (the base map) can't be loaded, e.g. no connection or the map service is down. */
export async function renderMap(el, points, onFail = () => {}) {
    const map = new Map({
        container: el,
        style: el.dataset.style || STYLE,
        ...PAKISTAN,
        pitch: points.length ? PITCH : 0,
        maxPitch: 60,
        attributionControl: false,
        cooperativeGestures: true, // page scroll stays normal; two fingers (or Ctrl + scroll) move the map
    });
    // Before the first load, an error means the base map itself failed; later errors (a missing tile) are harmless
    map.on('error', () => map.isStyleLoaded() || onFail());
    map.addControl(new NavigationControl({ visualizePitch: true }), 'bottom-right');
    map.addControl(new AttributionControl({ compact: true }), 'bottom-left'); // the style supplies the OpenFreeMap / OpenMapTiles / OpenStreetMap credit

    if (points.length) {
        map.fitBounds(openingBounds(points), { padding: 70, maxZoom: 13, pitch: PITCH, duration: 0 });
    }

    let keep = null;
    let apply = null;

    map.on('load', () => {
        englishLabels(map);
        // Start with the map credit collapsed to its (i) button so it doesn't cover the map on phones
        el.querySelector('.maplibregl-ctrl-attrib')?.classList.remove('maplibregl-compact-show');

        map.addSource('projects', {
            type: 'geojson',
            cluster: true,
            clusterRadius: 40, // group only pins that would really overlap; the rest show at their exact spots
            clusterMaxZoom: MAX_ZOOM_IN,
            clusterProperties: { image: [['coalesce', ['accumulated'], ['get', 'image']], ['get', 'image']] }, // a photo for each group
            data: features(points, keep),
        });
        // Invisible layer so the source is laid out; the markers themselves are HTML
        map.addLayer({ id: 'projects-hit', type: 'circle', source: 'projects', paint: { 'circle-radius': 0, 'circle-opacity': 0 } });

        let shown = {};
        const update = () => {
            const next = {};
            map.querySourceFeatures('projects').forEach((f) => {
                const group = f.properties.cluster;
                const key = group ? 'c' + f.properties.cluster_id : 'p' + f.properties.i;
                if (next[key]) return;
                let marker = shown[key];
                if (!marker) {
                    const p = group ? null : points[f.properties.i];
                    const el = markerElement(f.properties.image, group ? 'Several projects here: zoom in' : p.name, group);
                    marker = new Marker({ element: el, anchor: 'bottom' }).setLngLat(f.geometry.coordinates); // the tip is the exact spot
                    if (group) {
                        el.addEventListener('click', async () => {
                            const source = map.getSource('projects');
                            const zoom = await source.getClusterExpansionZoom(f.properties.cluster_id);
                            if (zoom < MAX_ZOOM_IN && map.getZoom() < MAX_ZOOM_IN - 0.5) {
                                map.easeTo({ center: f.geometry.coordinates, zoom: Math.min(zoom + 0.5, MAX_ZOOM_IN) });
                                return;
                            }
                            // Projects at (almost) the same spot never split apart: list them instead
                            const leaves = await source.getClusterLeaves(f.properties.cluster_id, 12, 0);
                            map.easeTo({ center: f.geometry.coordinates, duration: 400 }); // centred, so the list never opens off the edge
                            map.once('moveend', () => new Popup({ offset: 62, maxWidth: '300px', className: 'mp-popup', closeButton: false })
                                .setLngLat(f.geometry.coordinates).setHTML(list(leaves.map((l) => points[l.properties.i]))).addTo(map));
                        });
                    } else {
                        marker.setPopup(new Popup({ offset: 58, maxWidth: '280px', className: 'mp-popup', closeButton: false }).setHTML(card(p)));
                        // Centre the project so its card never opens off the edge of the map
                        el.addEventListener('click', () => map.easeTo({ center: f.geometry.coordinates, duration: 400 }));
                    }
                    marker.addTo(map);
                }
                next[key] = marker;
            });
            Object.keys(shown).forEach((key) => next[key] || shown[key].remove());
            shown = next;
        };
        map.on('data', (e) => e.sourceId === 'projects' && e.isSourceLoaded && update());
        map.on('moveend', update);
        update();

        // Filtered pages (developer page): swap the markers and fit the map to what is left
        apply = () => {
            Object.values(shown).forEach((m) => m.remove()); // group ids are reused by the new data, so start clean
            shown = {};
            map.getSource('projects').setData(features(points, keep));
            const left = keep ? points.filter((p) => keep.has(p.slug)) : points;
            if (left.length) map.fitBounds(openingBounds(left), { padding: 70, maxZoom: 13, pitch: PITCH });
        };
    });

    /** Show only these projects (a Set of slugs), or all of them with null. */
    map.showOnly = (slugs) => {
        keep = slugs;
        apply?.();
    };

    return map;
}
