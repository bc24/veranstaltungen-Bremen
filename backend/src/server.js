require("dotenv").config();
const express = require("express");
const cors = require("cors");
const { sequelize } = require("./models");
const marketsRouter = require("./routes/markets");
const { router: paymentsRouter, stripeWebhookHandler } = require("./routes/payments");
const adminRouter = require("./routes/admin");
const asyncHandler = require("./middleware/asyncHandler");

const app = express();

app.use(cors({ origin: process.env.FRONTEND_URL || "*" }));

// Der Stripe-Webhook benoetigt den rohen Request-Body fuer die Signaturpruefung
// und muss deshalb VOR express.json() gemountet werden.
app.post(
  "/api/payments/webhook",
  express.raw({ type: "application/json" }),
  asyncHandler(stripeWebhookHandler)
);

app.use(express.json());

app.use("/api/markets", marketsRouter);
app.use("/api/payments", paymentsRouter);
app.use("/api/admin", adminRouter);

app.get("/api/health", (req, res) => res.json({ status: "ok" }));

app.use((err, req, res, next) => {
  console.error(err);
  res.status(500).json({ error: "Interner Serverfehler." });
});

const PORT = process.env.PORT || 4000;

sequelize
  .sync()
  .then(() => {
    app.listen(PORT, () => console.log(`API laeuft auf Port ${PORT}`));
  })
  .catch((err) => {
    console.error("Datenbankverbindung fehlgeschlagen:", err);
    process.exit(1);
  });

module.exports = app;
