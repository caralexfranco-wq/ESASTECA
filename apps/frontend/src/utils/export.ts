import * as XLSX from 'xlsx';
import { saveAs } from 'file-saver';

export function exportExcel(rows: unknown[], name: string) {
  const ws = XLSX.utils.json_to_sheet(rows);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Datos');
  const buffer = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
  saveAs(new Blob([buffer]), `${name}.xlsx`);
}

export function exportCsv(rows: unknown[], name: string) {
  const ws = XLSX.utils.json_to_sheet(rows);
  const csv = XLSX.utils.sheet_to_csv(ws);
  saveAs(new Blob([csv], { type: 'text/csv;charset=utf-8' }), `${name}.csv`);
}
