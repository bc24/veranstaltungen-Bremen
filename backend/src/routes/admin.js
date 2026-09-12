const express = require("express");
const bcrypt = require("bcrypt");
const jwt = require("jsonwebtoken");
const { Admin, Market } = require("../models");
const { requireAdmin } = require("../middleware/auth");
const asyncHandler = require("../middleware/asyncHandler");

const router = express.Router();

// POST /api/admin/login
router.post("/login", asyncHandler(async (req, res) => {
  const { email, password } = req.body;
  if (!email || !password) {
    return res.status(400).json({ error: "E-Mail und Passwort sind erforderlich." });
  }

  const admin = await Admin.findOne({ where: { email } });
  if (!admin) {
    return res.status(401).json({ error: "Ungueltige Anmeldedaten." });
  }

  const valid = await bcrypt.compare(password, admin.passwordHash);
  if (!valid) {
    return res.status(401).json({ error: "Ungueltige Anmeldedaten." });
  }

  const token = jwt.sign({ id: admin.id, email: admin.email }, process.env.JWT_SECRET, {
    expiresIn: "8h",
  });

  res.json({ token });
}));

// Alle folgenden Routen erfordern ein gueltiges Admin-Token.
router.use(requireAdmin);

// GET /api/admin/markets?status=pending_review - Kuratierungs-Warteschlange (nur bezahlte Eintraege
// erreichen ueberhaupt diesen Status, siehe payments.js Webhook).
router.get("/markets", asyncHandler(async (req, res) => {
  const { status } = req.query;
  const where = status ? { status } : {};
  const markets = await Market.findAll({ where, order: [["createdAt", "DESC"]] });
  res.json(markets);
}));

// PATCH /api/admin/markets/:id - Eintrag veroeffentlichen oder ablehnen
router.patch("/markets/:id", asyncHandler(async (req, res) => {
  const { status, rejectionReason } = req.body;
  if (!["published", "rejected", "pending_review"].includes(status)) {
    return res.status(400).json({ error: "Ungueltiger Status." });
  }

  const market = await Market.findByPk(req.params.id);
  if (!market) {
    return res.status(404).json({ error: "Markt nicht gefunden." });
  }
  if (market.paymentStatus !== "paid") {
    return res.status(409).json({ error: "Eintrag wurde noch nicht bezahlt und kann nicht veroeffentlicht werden." });
  }

  market.status = status;
  market.rejectionReason = status === "rejected" ? rejectionReason || null : null;
  await market.save();

  res.json(market);
}));

// DELETE /api/admin/markets/:id
router.delete("/markets/:id", asyncHandler(async (req, res) => {
  const market = await Market.findByPk(req.params.id);
  if (!market) {
    return res.status(404).json({ error: "Markt nicht gefunden." });
  }
  await market.destroy();
  res.status(204).send();
}));

module.exports = router;
