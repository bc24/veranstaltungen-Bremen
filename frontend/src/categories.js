export const CATEGORY_LABELS = {
  kuerbismarkt: "Kürbismarkt",
  weihnachtsmarkt: "Weihnachtsmarkt",
  wochenmarkt: "Wochenmarkt",
  flohmarkt: "Flohmarkt",
  bauernmarkt: "Bauernmarkt",
  sonstiges: "Sonstiges",
};

export const CATEGORY_OPTIONS = Object.entries(CATEGORY_LABELS).map(([value, label]) => ({
  value,
  label,
}));
