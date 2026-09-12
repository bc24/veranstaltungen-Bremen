(function () {
  var markets = window.MARKETS || [];
  var BREMEN_CENTER = [53.0793, 8.8017];
  var center = markets.length > 0 ? [markets[0].latitude, markets[0].longitude] : BREMEN_CENTER;

  var map = L.map("map").setView(center, 11);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>-Mitwirkende',
  }).addTo(map);

  markets.forEach(function (m) {
    var marker = L.marker([m.latitude, m.longitude]).addTo(map);
    marker.bindPopup(
      "<strong>" + escapeHtml(m.name) + "</strong><br>" +
      escapeHtml(m.categoryLabel) + "<br>" +
      escapeHtml(m.street) + ", " + escapeHtml(m.postalCode) + " " + escapeHtml(m.city)
    );
    marker.on("click", function () {
      var card = document.getElementById("market-" + m.id);
      if (card) card.scrollIntoView({ behavior: "smooth", block: "center" });
    });
  });

  function escapeHtml(str) {
    var div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
  }
})();
