require("dotenv").config();
const bcrypt = require("bcrypt");
const { sequelize, Admin, Market } = require("./models");

async function seed() {
  await sequelize.sync();

  const adminEmail = process.env.ADMIN_EMAIL;
  const adminPassword = process.env.ADMIN_PASSWORD;
  if (!adminEmail || !adminPassword) {
    throw new Error("ADMIN_EMAIL und ADMIN_PASSWORD muessen in .env gesetzt sein.");
  }

  const existingAdmin = await Admin.findOne({ where: { email: adminEmail } });
  if (!existingAdmin) {
    const passwordHash = await bcrypt.hash(adminPassword, 10);
    await Admin.create({ email: adminEmail, passwordHash });
    console.log(`Admin-Konto angelegt: ${adminEmail}`);
  } else {
    console.log(`Admin-Konto existiert bereits: ${adminEmail}`);
  }

  const sampleCount = await Market.count();
  if (sampleCount === 0) {
    await Market.bulkCreate([
      {
        name: "Bremer Kuerbismarkt Findorff",
        category: "kuerbismarkt",
        description:
          "Ueber 100 Kuerbissorten, Kuerbis-Schnitzstand fuer Kinder und regionale Herbstspezialitaeten.",
        street: "Nordstr. 12",
        postalCode: "28217",
        city: "Bremen",
        latitude: 53.0951,
        longitude: 8.7869,
        startDate: "2026-10-03",
        endDate: "2026-10-31",
        openingHours: "Mo-So 10:00-18:00",
        website: "https://example.org/kuerbismarkt-findorff",
        contactEmail: "info@kuerbismarkt-findorff.example",
        contactName: "Anke Meyer",
        priceAmount: 4900,
        priceCurrency: "eur",
        paymentStatus: "paid",
        stripeSessionId: "seed_demo_session_1",
        status: "published",
      },
      {
        name: "Weihnachtsmarkt Schlachte",
        category: "weihnachtsmarkt",
        description:
          "Historischer Weihnachtsmarkt direkt an der Weser mit Gluehwein, Kunsthandwerk und Live-Musik.",
        street: "Schlachte",
        postalCode: "28195",
        city: "Bremen",
        latitude: 53.0757,
        longitude: 8.8017,
        startDate: "2026-11-23",
        endDate: "2026-12-30",
        openingHours: "Mo-So 11:00-21:00",
        website: "https://example.org/weihnachtsmarkt-schlachte",
        contactEmail: "kontakt@schlachte-weihnacht.example",
        contactName: "Jan Hendricks",
        priceAmount: 4900,
        priceCurrency: "eur",
        paymentStatus: "paid",
        stripeSessionId: "seed_demo_session_2",
        status: "published",
      },
      {
        name: "Kuerbisfest Werderland",
        category: "kuerbismarkt",
        description: "Kuerbisfeld zum Selberpfluecken, Traktorfahrten und Hofcafe.",
        street: "Werderlandstr. 5",
        postalCode: "28757",
        city: "Bremen",
        latitude: 53.1685,
        longitude: 8.6423,
        startDate: "2026-09-26",
        endDate: "2026-11-01",
        openingHours: "Sa-So 09:00-17:00",
        website: null,
        contactEmail: "hof@werderland.example",
        contactName: "Frieda Boelken",
        priceAmount: 4900,
        priceCurrency: "eur",
        paymentStatus: "paid",
        stripeSessionId: "seed_demo_session_3",
        status: "pending_review",
      },
    ]);
    console.log("Beispiel-Markteintraege angelegt.");
  } else {
    console.log("Markteintraege existieren bereits, ueberspringe Beispieldaten.");
  }

  await sequelize.close();
}

seed().catch((err) => {
  console.error(err);
  process.exit(1);
});
