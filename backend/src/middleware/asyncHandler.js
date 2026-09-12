// Express 4 faengt rejections aus async-Handlern nicht automatisch ab - ohne diesen
// Wrapper wuerde ein einzelner Fehler (z.B. ein ungueltiger Stripe-Call) den gesamten
// Prozess mit einer unhandled rejection abstuerzen lassen.
function asyncHandler(fn) {
  return (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);
}

module.exports = asyncHandler;
