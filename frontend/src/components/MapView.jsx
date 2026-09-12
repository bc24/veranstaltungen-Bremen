import { MapContainer, TileLayer, Marker, Popup } from "react-leaflet";
import L from "leaflet";
import markerIcon2x from "leaflet/dist/images/marker-icon-2x.png";
import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";
import { CATEGORY_LABELS } from "../categories.js";

// Vite bundelt Leaflets Standard-Marker-Bilder nicht automatisch mit relativen Pfaden,
// daher werden sie hier explizit importiert und dem Default-Icon zugewiesen.
const defaultIcon = L.icon({
  iconUrl: markerIcon,
  iconRetinaUrl: markerIcon2x,
  shadowUrl: markerShadow,
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41],
});

const BREMEN_CENTER = [53.0793, 8.8017];

export default function MapView({ markets, selectedId, onSelect }) {
  const center =
    markets.length > 0
      ? [Number(markets[0].latitude), Number(markets[0].longitude)]
      : BREMEN_CENTER;

  return (
    <MapContainer center={center} zoom={11} className="map-view" scrollWheelZoom>
      <TileLayer
        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>-Mitwirkende'
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
      />
      {markets.map((m) => (
        <Marker
          key={m.id}
          position={[Number(m.latitude), Number(m.longitude)]}
          icon={defaultIcon}
          eventHandlers={{ click: () => onSelect?.(m.id) }}
        >
          <Popup>
            <strong>{m.name}</strong>
            <br />
            {CATEGORY_LABELS[m.category] || m.category}
            <br />
            {m.street}, {m.postalCode} {m.city}
          </Popup>
        </Marker>
      ))}
    </MapContainer>
  );
}
