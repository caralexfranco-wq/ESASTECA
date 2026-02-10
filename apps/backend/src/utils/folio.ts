import dayjs from 'dayjs';

export function generateFolio(sequence: number): string {
  return `EXP-${dayjs().format('YYYY')}-${String(sequence).padStart(5, '0')}`;
}
