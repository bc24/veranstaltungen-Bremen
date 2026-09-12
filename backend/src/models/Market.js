const { DataTypes } = require("sequelize");
const sequelize = require("../config/db");

// Lebenszyklus eines Eintrags:
// pending_payment -> (Stripe Checkout erfolgreich) -> pending_review -> published | rejected
const Market = sequelize.define(
  "Market",
  {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true,
      autoIncrement: true,
    },
    name: { type: DataTypes.STRING, allowNull: false },
    category: {
      type: DataTypes.ENUM(
        "kuerbismarkt",
        "weihnachtsmarkt",
        "wochenmarkt",
        "flohmarkt",
        "bauernmarkt",
        "sonstiges"
      ),
      allowNull: false,
    },
    description: { type: DataTypes.TEXT, allowNull: false },
    street: { type: DataTypes.STRING, allowNull: false },
    postalCode: { type: DataTypes.STRING, allowNull: false },
    city: { type: DataTypes.STRING, allowNull: false },
    latitude: { type: DataTypes.DECIMAL(9, 6), allowNull: false },
    longitude: { type: DataTypes.DECIMAL(9, 6), allowNull: false },
    startDate: { type: DataTypes.DATEONLY, allowNull: false },
    endDate: { type: DataTypes.DATEONLY, allowNull: false },
    openingHours: { type: DataTypes.STRING, allowNull: true },
    website: { type: DataTypes.STRING, allowNull: true },
    contactEmail: { type: DataTypes.STRING, allowNull: false },
    contactName: { type: DataTypes.STRING, allowNull: false },

    // Zahlungs- und Freigabestatus - es gibt bewusst keine kostenlose Basisversion:
    // jeder Eintrag durchlaeuft zwingend paymentStatus "paid", bevor er ueberhaupt
    // zur redaktionellen Pruefung (status) vorgelegt wird.
    paymentStatus: {
      type: DataTypes.ENUM("pending", "paid", "failed"),
      allowNull: false,
      defaultValue: "pending",
    },
    priceAmount: { type: DataTypes.INTEGER, allowNull: false }, // in Cent
    priceCurrency: { type: DataTypes.STRING, allowNull: false, defaultValue: "eur" },
    stripeSessionId: { type: DataTypes.STRING, allowNull: true },
    stripePaymentIntentId: { type: DataTypes.STRING, allowNull: true },

    status: {
      type: DataTypes.ENUM("pending_payment", "pending_review", "published", "rejected"),
      allowNull: false,
      defaultValue: "pending_payment",
    },
    rejectionReason: { type: DataTypes.STRING, allowNull: true },
  },
  {
    tableName: "markets",
    timestamps: true,
  }
);

module.exports = Market;
