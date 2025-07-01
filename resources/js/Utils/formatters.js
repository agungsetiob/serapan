// export const formatCurrency = (value) => {
//   if (value === null || value === undefined) return 'Rp0';
//   return new Intl.NumberFormat('id-ID', {
//     style: 'currency',
//     currency: 'IDR',
//     minimumFractionDigits: 0,
//     maximumFractionDigits: 2
//   }).format(value);
// };

export function formatNumber(value) {
  if (typeof value !== 'number') return '0,00';
  return new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
}

// export const formatDate = (dateString) => {
//   if (!dateString) return '';
//   const options = { year: 'numeric', month: 'short', day: 'numeric' };
//   return new Date(dateString).toLocaleDateString('id-ID', options);
// };

export const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};