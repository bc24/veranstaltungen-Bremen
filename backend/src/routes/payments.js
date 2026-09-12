const express = require("express");
const Stripe = require("stripe");
const { Market } = require("../models");
const asyncHandler = require("../middleware/asyncHandler");

const router = express.Router();
const stripe = new Stripe(process.env.STRIPE_SECRET_KEY);

// POST /api/payments/create-checkout-session
// Erstellt eine Stripe Checkout Session fuer die einmalige Listing-Gebuehr.
// Es gibt bewusst keinen kostenlosen Pfad: ohne erfolgreiche Zahlung bleibt
// der Eintrag dauerhaft im Status "pending_payment" und wird nie oeffentlich gelistet.
router.post("/create-checkout-session", asyncHandler(async (req, res) => {
  const { marketId } = req.body;
  if (!marketId) {
    return res.status(400).json({ error: "marketId fehlt." });
  }

  const market = await Market.findByPk(marketId);
  if (!market) {
    return res.status(404).json({ error: "Markt nicht gefunden." });
  }
  if (market.status !== "pending_payment") {
    return res.status(409).json({ error: "Fuer diesen Eintrag wurde bereits bezahlt oder er wird geprueft." });
  }

  const session = await stripe.checkout.sessions.create({
    mode: "payment",
    payment_method_types: ["card"],
    line_items: [
      {
        price_data: {
          currency: market.priceCurrency,
          unit_amount: market.priceAmount,
          product_data: {
            name: `Markteintrag: ${market.name}`,
            description: "Einmalige Gebuehr fuer die kuratierte Aufnahme in das Marktverzeichnis.",
          },
        },
        quantity: 1,
      },
    ],
    metadata: { marketId: String(market.id) },
    success_url: `${process.env.FRONTEND_URL}/einreichen/erfolg?market_id=${market.id}`,
    cancel_url: `${process.env.FRONTEND_URL}/einreichen/abgebrochen?market_id=${market.id}`,
  });

  market.stripeSessionId = session.id;
  await market.save();

  res.json({ url: session.url });
}));

// Stripe-Webhook-Handler. Wird in server.js separat VOR express.json() mit
// express.raw() gemountet, da Stripe fuer die Signaturpruefung den rohen Body braucht.
async function stripeWebhookHandler(req, res) {
  const signature = req.headers["stripe-signature"];
  let event;

  try {
    event = stripe.webhooks.constructEvent(req.body, signature, process.env.STRIPE_WEBHOOK_SECRET);
  } catch (err) {
    return res.status(400).send(`Webhook-Signatur ungueltig: ${err.message}`);
  }

  if (event.type === "checkout.session.completed") {
    const session = event.data.object;
    const marketId = session.metadata?.marketId;
    if (marketId) {
      const market = await Market.findByPk(marketId);
      if (market && market.status === "pending_payment") {
        market.paymentStatus = "paid";
        market.status = "pending_review"; // wartet jetzt auf redaktionelle Kuration
        market.stripePaymentIntentId = session.payment_intent || null;
        await market.save();
      }
    }
  }

  res.json({ received: true });
}

module.exports = { router, stripeWebhookHandler };
