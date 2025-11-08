import { Moon } from "lucide-react";

export function AttendanceHeader() {
  return (
    <div className="border-b bg-white">
      <div className="container mx-auto px-6 py-6">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="bg-emerald-600 p-3 rounded-lg">
              <Moon className="w-6 h-6 text-white" />
            </div>
            <div>
              <h1 className="text-gray-900">Sistem Absensi Ustadz & Ustadzah</h1>
              <p className="text-gray-600 text-sm">Madrasah Diniyyah - Pencatatan Kehadiran Pengajar</p>
            </div>
          </div>
          <div className="text-right">
            <p className="text-sm text-gray-600">Minggu Ini</p>
            <p className="text-gray-900">{new Date().toLocaleDateString("id-ID", { day: "numeric", month: "long", year: "numeric" })}</p>
          </div>
        </div>
      </div>
    </div>
  );
}
