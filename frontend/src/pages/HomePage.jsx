import { useEffect, useState } from "react";
import MapView from "../components/MapView.jsx";
import Filters from "../components/Filters.jsx";
import MarketCard from "../components/MarketCard.jsx";
import { fetchMarkets } from "../api/client.js";

const EMPTY_FILTERS = { q: "", category: "", city: "", from: "", to: "" };

export default function HomePage() {
  const [filters, setFilters] = useState(EMPTY_FILTERS);
  const [markets, setMarkets] = useState([]);
  const [selectedId, setSelectedId] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    setError(null);

    fetchMarkets(filters)
      .then((data) => {
        if (!cancelled) setMarkets(data);
      })
      .catch((err) => {
        if (!cancelled) setError(err.message);
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, [filters]);

  return (
    <div className="home-page">
      <Filters filters={filters} onChange={setFilters} />

      <div className="home-layout">
        <div className="market-list">
          <div className="market-list-count">
            {loading ? "Lade Märkte …" : `${markets.length} Markt${markets.length === 1 ? "" : "märkte"} gefunden`}
          </div>
          {error && <div className="error-box">{error}</div>}
          {!loading && markets.length === 0 && !error && (
            <div className="empty-box">
              <p>Keine Märkte gefunden. Ändere die Filter oder trage selbst einen Markt ein.</p>
            </div>
          )}
          {markets.map((m) => (
            <MarketCard
              key={m.id}
              market={m}
              active={m.id === selectedId}
              onSelect={setSelectedId}
            />
          ))}
        </div>
        <div className="market-map">
          <MapView markets={markets} selectedId={selectedId} onSelect={setSelectedId} />
        </div>
      </div>
    </div>
  );
}
