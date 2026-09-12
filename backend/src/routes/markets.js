const express = require("express");
const { Op } = require("sequelize");
const { Market } = require("../models");
const asyncHandler = require("../middleware/asyncHandler");

const router = express.Router();

const PUBLIC_ATTRIBUTES = [
  "id",
  "name",
  "category",
  "description",
  "street",
  "postalCode",
  "city",
  "latitude",
  "longitude",
  "startDate",
  "endDate",
  "openingHours",
  "website",
  "createdAt",
];

// GET /api/markets - oeffentliches Verzeichnis, nur veroeffentlichte, kuratierte Eintraege
router.get("/", asyncHandler(async (req, res) => {
  const { category, city, from, to, q } = req.query;
  const where = { status: "published" };

  if (category) where.category = category;
  if (city) where.city = { [Op.like]: `%${city}%` };
  if (from) where.endDate = { [Op.gte]: from };
  if (to) where.startDate = { ...(where.startDate || {}), [Op.lte]: to };
  if (q) {
    where[Op.or] = [
      { name: { [Op.like]: `%${q}%` } },
      { description: { [Op.like]: `%${q}%` } },
      { city: { [Op.like]: `%${q}%` } },
    ];
  }

  const markets = await Market.findAll({
    where,
    attributes: PUBLIC_ATTRIBUTES,
    order: [["startDate", "ASC"]],
  });

  res.json(markets);
}));

// GET /api/markets/:id - einzelner veroeffentlichter Eintrag
router.get("/:id", asyncHandler(async (req, res) => {
  const market = await Market.findOne({
    where: { id: req.params.id, status: "published" },
    attributes: PUBLIC_ATTRIBUTES,
  });

  if (!market) {
    return res.status(404).json({ error: "Markt nicht gefunden." });
  }

  res.json(market);
}));

// POST /api/markets - neuen (unbezahlten) Markteintrag anlegen.
// Wird direkt gefolgt von POST /api/payments/create-checkout-session.
router.post("/", asyncHandler(async (req, res) => {
  const {
    name,
    category,
    description,
    street,
    postalCode,
    city,
    latitude,
    longitude,
    startDate,
    endDate,
    openingHours,
    website,
    contactEmail,
    contactName,
  } = req.body;

  const required = {
    name,
    category,
    description,
    street,
    postalCode,
    city,
    latitude,
    longitude,
    startDate,
    endDate,
    contactEmail,
    contactName,
  };
  const missing = Object.entries(required)
    .filter(([, value]) => value === undefined || value === null || value === "")
    .map(([key]) => key);

  if (missing.length > 0) {
    return res.status(400).json({ error: `Pflichtfelder fehlen: ${missing.join(", ")}` });
  }

  const priceAmount = parseInt(process.env.LISTING_PRICE_AMOUNT, 10) || 4900;
  const priceCurrency = process.env.LISTING_CURRENCY || "eur";

  const market = await Market.create({
    name,
    category,
    description,
    street,
    postalCode,
    city,
    latitude,
    longitude,
    startDate,
    endDate,
    openingHours,
    website,
    contactEmail,
    contactName,
    priceAmount,
    priceCurrency,
    status: "pending_payment",
    paymentStatus: "pending",
  });

  res.status(201).json({ id: market.id, priceAmount, priceCurrency });
}));

module.exports = router;
