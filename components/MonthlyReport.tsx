import { useState, useRef } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Badge } from "./ui/badge";
import { Button } from "./ui/button";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "./ui/table";
import { Calendar, Download, FileText, TrendingUp, TrendingDown, CheckCircle2, XCircle, Clock, Users } from "lucide-react";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";
import { allTeachers, getTeacherSchedule } from "../data/mockData";
import { AttendanceStatus, Teacher } from "../types/attendance";

// Declare modules in types/ambient.d.ts instead of inline to avoid TS augmentation errors in module files
// See: ../types/ambient.d.ts

interface MonthlyStats {
  teacherId: string;
  teacher: Teacher;
  totalSessions: number;
  present: number;
  absent: number;
  late: number;
  izin: number;
  attendanceRate: number;
  scheduledClasses: number;
}

const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

export function MonthlyReport() {
  const currentDate = new Date();
  const [selectedMonth, setSelectedMonth] = useState(currentDate.getMonth());
  const [selectedYear, setSelectedYear] = useState(currentDate.getFullYear());
  const [filterStatus, setFilterStatus] = useState<"all" | "good" | "warning" | "poor">("all");
  const tableRef = useRef<HTMLDivElement>(null);

  // Generate monthly stats for all teachers
  const generateMonthlyStats = (): MonthlyStats[] => {
    const stats: MonthlyStats[] = [];

    allTeachers.forEach((teacher) => {
      const schedule = getTeacherSchedule(teacher.id);

      // Calculate days in selected month
      const daysInMonth = new Date(selectedYear, selectedMonth + 1, 0).getDate();
      const scheduledDays = schedule.length > 0 ? daysInMonth : 0;
      const totalSessions = schedule.length * 4; // Approx 4 weeks per month

      // Generate random attendance (in production, this would come from database)
      const present = Math.floor(totalSessions * (0.75 + Math.random() * 0.2));
      const late = Math.floor(totalSessions * (Math.random() * 0.1));
      const izin = Math.floor(totalSessions * (Math.random() * 0.05));
      const absent = totalSessions - present - late - izin;

      const attendanceRate = totalSessions > 0 ? (present / totalSessions) * 100 : 0;

      stats.push({
        teacherId: teacher.id,
        teacher,
        totalSessions,
        present,
        absent,
        late,
        izin,
        attendanceRate,
        scheduledClasses: schedule.length,
      });
    });

    return stats.sort((a, b) => b.attendanceRate - a.attendanceRate);
  };

  const monthlyStats = generateMonthlyStats();

  // Filter based on attendance rate
  const filteredStats = monthlyStats.filter((stat) => {
    if (filterStatus === "all") return true;
    if (filterStatus === "good") return stat.attendanceRate >= 90;
    if (filterStatus === "warning") return stat.attendanceRate >= 70 && stat.attendanceRate < 90;
    if (filterStatus === "poor") return stat.attendanceRate < 70;
    return true;
  });

  // Calculate overall statistics
  const overallStats = {
    totalTeachers: allTeachers.length,
    totalSessions: monthlyStats.reduce((sum, s) => sum + s.totalSessions, 0),
    totalPresent: monthlyStats.reduce((sum, s) => sum + s.present, 0),
    totalAbsent: monthlyStats.reduce((sum, s) => sum + s.absent, 0),
    averageAttendance: monthlyStats.reduce((sum, s) => sum + s.attendanceRate, 0) / monthlyStats.length,
    goodPerformers: monthlyStats.filter((s) => s.attendanceRate >= 90).length,
    needsAttention: monthlyStats.filter((s) => s.attendanceRate < 70).length,
  };

  const getAttendanceColor = (rate: number) => {
    if (rate >= 90) return "text-green-600";
    if (rate >= 70) return "text-yellow-600";
    return "text-red-600";
  };

  const getAttendanceBadge = (rate: number) => {
    if (rate >= 90) {
      return <Badge className="bg-green-100 text-green-800 hover:bg-green-100">Baik Sekali</Badge>;
    }
    if (rate >= 70) {
      return <Badge className="bg-yellow-100 text-yellow-800 hover:bg-yellow-100">Perlu Ditingkatkan</Badge>;
    }
    return <Badge className="bg-red-100 text-red-800 hover:bg-red-100">Perlu Perhatian</Badge>;
  };

  const handleExportPDF = async () => {
    if (!tableRef.current) return;

    // Dynamic import with fallback to default export if present
    const jsPDFModule: any = await import("jspdf");
    const jsPDF = jsPDFModule.default ?? jsPDFModule;

    const html2canvasModule: any = await import("html2canvas");
    const html2canvas = html2canvasModule.default ?? html2canvasModule;

    try {
      // Create canvas from table
      const canvas = await html2canvas(tableRef.current, {
        scale: 2,
        logging: false,
        backgroundColor: "#ffffff",
      });

      // Calculate PDF dimensions
      const imgWidth = 210; // A4 width in mm
      const imgHeight = (canvas.height * imgWidth) / canvas.width;

      // Create PDF
      const pdf = new jsPDF("p", "mm", "a4");
      const imgData = canvas.toDataURL("image/png");

      // Add title
      pdf.setFontSize(16);
      pdf.text("Rekapan Absensi Bulanan", 105, 15, {
        align: "center",
      });
      pdf.setFontSize(12);
      pdf.text(`${monthNames[selectedMonth]} ${selectedYear}`, 105, 22, { align: "center" });

      // Add image
      pdf.addImage(imgData, "PNG", 0, 30, imgWidth, imgHeight);

      // Add footer
      const totalPages = Math.ceil(imgHeight / 270); // A4 height ≈ 270mm
      for (let i = 1; i <= totalPages; i++) {
        if (i > 1) {
          pdf.addPage();
          pdf.addImage(imgData, "PNG", 0, -(270 * (i - 1)) + 30, imgWidth, imgHeight);
        }
        pdf.setFontSize(10);
        pdf.text(`Halaman ${i} dari ${totalPages} | Dicetak: ${new Date().toLocaleDateString("id-ID")}`, 105, 287, { align: "center" });
      }

      // Save PDF
      pdf.save(`Rekapan-Absensi-${monthNames[selectedMonth]}-${selectedYear}.pdf`);
    } catch (error) {
      console.error("Error generating PDF:", error);
      alert("Gagal membuat PDF. Silakan coba lagi.");
    }
  };

  const years = Array.from({ length: 5 }, (_, i) => currentDate.getFullYear() - 2 + i);

  return (
    <div className="space-y-6">
      {/* Header Controls */}
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <CardTitle className="flex items-center gap-2">
              <Calendar className="w-5 h-5 text-emerald-600" />
              Rekapan Absensi Bulanan
            </CardTitle>
            <Button onClick={handleExportPDF} className="bg-emerald-600 hover:bg-emerald-700">
              <Download className="w-4 h-4 mr-2" />
              Export PDF
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className="text-sm text-gray-600 mb-2 block">Bulan</label>
              <Select value={selectedMonth.toString()} onValueChange={(value) => setSelectedMonth(parseInt(value))}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {monthNames.map((month, index) => (
                    <SelectItem key={index} value={index.toString()}>
                      {month}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div>
              <label className="text-sm text-gray-600 mb-2 block">Tahun</label>
              <Select value={selectedYear.toString()} onValueChange={(value) => setSelectedYear(parseInt(value))}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {years.map((year) => (
                    <SelectItem key={year} value={year.toString()}>
                      {year}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div>
              <label className="text-sm text-gray-600 mb-2 block">Filter Status</label>
              <Select value={filterStatus} onValueChange={(value: any) => setFilterStatus(value)}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Semua Status</SelectItem>
                  <SelectItem value="good">Baik Sekali (≥90%)</SelectItem>
                  <SelectItem value="warning">Perlu Ditingkatkan (70-89%)</SelectItem>
                  <SelectItem value="poor">Perlu Perhatian {"(<70%)"}</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Overall Statistics */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-start justify-between">
              <div>
                <p className="text-sm text-gray-600 mb-1">Total Pengajar</p>
                <p className="text-2xl text-gray-900">{overallStats.totalTeachers}</p>
              </div>
              <Users className="w-8 h-8 text-blue-600 opacity-80" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="pt-6">
            <div className="flex items-start justify-between">
              <div>
                <p className="text-sm text-gray-600 mb-1">Rata-rata Kehadiran</p>
                <p className={`text-2xl ${getAttendanceColor(overallStats.averageAttendance)}`}>{overallStats.averageAttendance.toFixed(1)}%</p>
              </div>
              <TrendingUp className="w-8 h-8 text-green-600 opacity-80" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="pt-6">
            <div className="flex items-start justify-between">
              <div>
                <p className="text-sm text-gray-600 mb-1">Performa Baik</p>
                <p className="text-2xl text-green-600">{overallStats.goodPerformers}</p>
              </div>
              <CheckCircle2 className="w-8 h-8 text-green-600 opacity-80" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="pt-6">
            <div className="flex items-start justify-between">
              <div>
                <p className="text-sm text-gray-600 mb-1">Perlu Perhatian</p>
                <p className="text-2xl text-red-600">{overallStats.needsAttention}</p>
              </div>
              <XCircle className="w-8 h-8 text-red-600 opacity-80" />
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Monthly Report Table */}
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <div>
              <CardTitle>
                Tabel Rekapan - {monthNames[selectedMonth]} {selectedYear}
              </CardTitle>
              <p className="text-sm text-gray-600 mt-1">
                Menampilkan {filteredStats.length} dari {monthlyStats.length} pengajar
              </p>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div ref={tableRef} className="bg-white p-6">
            {/* Table Header for PDF */}
            <div className="text-center mb-6 print-only" style={{ display: "none" }}>
              <h1 className="text-xl mb-2">REKAPAN ABSENSI BULANAN</h1>
              <h2 className="text-lg">Madrasah Diniyyah</h2>
              <p className="text-sm text-gray-600">
                {monthNames[selectedMonth]} {selectedYear}
              </p>
            </div>

            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead className="w-12">#</TableHead>
                    <TableHead>Nama Ustadz/Ustadzah</TableHead>
                    <TableHead>Mata Pelajaran</TableHead>
                    <TableHead className="text-center">Jadwal Kelas</TableHead>
                    <TableHead className="text-center">Total Sesi</TableHead>
                    <TableHead className="text-center">Hadir</TableHead>
                    <TableHead className="text-center">Terlambat</TableHead>
                    <TableHead className="text-center">Izin</TableHead>
                    <TableHead className="text-center">Tidak Hadir</TableHead>
                    <TableHead className="text-center">Tingkat Kehadiran</TableHead>
                    <TableHead>Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredStats.map((stat, index) => (
                    <TableRow key={stat.teacherId}>
                      <TableCell>{index + 1}</TableCell>
                      <TableCell>
                        <div>
                          <p className="text-gray-900">{stat.teacher.name}</p>
                        </div>
                      </TableCell>
                      <TableCell className="text-sm">{stat.teacher.subject}</TableCell>
                      <TableCell className="text-center">
                        <Badge variant="outline">{stat.scheduledClasses} kelas</Badge>
                      </TableCell>
                      <TableCell className="text-center text-gray-900">{stat.totalSessions}</TableCell>
                      <TableCell className="text-center">
                        <span className="text-green-600">{stat.present}</span>
                      </TableCell>
                      <TableCell className="text-center">
                        <span className="text-yellow-600">{stat.late}</span>
                      </TableCell>
                      <TableCell className="text-center">
                        <span className="text-orange-600">{stat.izin}</span>
                      </TableCell>
                      <TableCell className="text-center">
                        <span className="text-red-600">{stat.absent}</span>
                      </TableCell>
                      <TableCell className="text-center">
                        <span className={`${getAttendanceColor(stat.attendanceRate)}`}>{stat.attendanceRate.toFixed(1)}%</span>
                      </TableCell>
                      <TableCell>{getAttendanceBadge(stat.attendanceRate)}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>

            {/* Summary Footer for PDF */}
            <div className="mt-6 pt-4 border-t print-only" style={{ display: "none" }}>
              <div className="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <p>
                    <strong>Total Pengajar:</strong> {overallStats.totalTeachers}
                  </p>
                  <p>
                    <strong>Total Sesi:</strong> {overallStats.totalSessions}
                  </p>
                </div>
                <div>
                  <p>
                    <strong>Rata-rata Kehadiran:</strong> {overallStats.averageAttendance.toFixed(1)}%
                  </p>
                  <p>
                    <strong>Performa Baik:</strong> {overallStats.goodPerformers} pengajar
                  </p>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Legend */}
      <Card>
        <CardHeader>
          <CardTitle>Keterangan Status Kehadiran</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="flex items-center gap-2">
              <div className="w-4 h-4 bg-green-100 border-2 border-green-600 rounded"></div>
              <span className="text-sm">Baik Sekali: Kehadiran ≥ 90%</span>
            </div>
            <div className="flex items-center gap-2">
              <div className="w-4 h-4 bg-yellow-100 border-2 border-yellow-600 rounded"></div>
              <span className="text-sm">Perlu Ditingkatkan: Kehadiran 70-89%</span>
            </div>
            <div className="flex items-center gap-2">
              <div className="w-4 h-4 bg-red-100 border-2 border-red-600 rounded"></div>
              <span className="text-sm">Perlu Perhatian: Kehadiran {"< 70%"}</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <style>{`
        @media print {
          .print-only {
            display: block !important;
          }
        }
      `}</style>
    </div>
  );
}
