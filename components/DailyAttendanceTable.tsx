import { DailyAttendance, AttendanceStatus, PrayerTime } from "../types/attendance";
import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Badge } from "./ui/badge";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "./ui/table";
import { Sunrise, Sun, Sunset, Moon } from "lucide-react";

interface DailyAttendanceTableProps {
  dailyRecords: DailyAttendance[];
}

const prayerTimeInfo: Record<PrayerTime, { label: string; icon: any; color: string; time: string }> = {
  subuh: { label: "Subuh", icon: Sunrise, color: "text-indigo-600", time: "04:30 - 06:00" },
  ashar: { label: "Ashar", icon: Sun, color: "text-amber-600", time: "15:30 - 17:00" },
  maghrib: { label: "Maghrib", icon: Sunset, color: "text-orange-600", time: "18:00 - 19:00" },
  isya: { label: "Isya", icon: Moon, color: "text-blue-900", time: "19:30 - 21:00" },
};

const getStatusBadge = (status: AttendanceStatus) => {
  const variants = {
    present: { variant: "default" as const, className: "bg-green-100 text-green-800 hover:bg-green-100" },
    absent: { variant: "destructive" as const, className: "bg-red-100 text-red-800 hover:bg-red-100" },
    late: { variant: "secondary" as const, className: "bg-yellow-100 text-yellow-800 hover:bg-yellow-100" },
    "half-day": { variant: "outline" as const, className: "bg-orange-100 text-orange-800 hover:bg-orange-100" },
  };

  const config = variants[status];
  const labels = {
    present: "Hadir",
    absent: "Tidak Hadir",
    late: "Terlambat",
    "half-day": "Izin",
  };

  return <Badge className={config.className}>{labels[status]}</Badge>;
};

export function DailyAttendanceTable({ dailyRecords }: DailyAttendanceTableProps) {
  const sortedRecords = [...dailyRecords].sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());

  return (
    <div className="space-y-4">
      {sortedRecords.map((record) => {
        const date = new Date(record.date);
        const dayName = date.toLocaleDateString("id-ID", { weekday: "long" });
        const formattedDate = date.toLocaleDateString("id-ID", { day: "numeric", month: "long", year: "numeric" });

        return (
          <Card key={record.date}>
            <CardHeader>
              <CardTitle>
                {dayName}, {formattedDate}
              </CardTitle>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Waktu</TableHead>
                    <TableHead>Jadwal</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Keterangan</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {record.prayers.map((prayerRecord) => {
                    const info = prayerTimeInfo[prayerRecord.prayerTime];
                    const Icon = info.icon;

                    return (
                      <TableRow key={prayerRecord.prayerTime}>
                        <TableCell>
                          <div className="flex items-center gap-2">
                            <Icon className={`w-5 h-5 ${info.color}`} />
                            <div>
                              <div>{info.label}</div>
                              <div className="text-xs text-gray-500">{info.time}</div>
                            </div>
                          </div>
                        </TableCell>
                        <TableCell className="text-gray-700">Kajian {info.label}</TableCell>
                        <TableCell>{getStatusBadge(prayerRecord.status)}</TableCell>
                        <TableCell className="text-gray-600">{prayerRecord.notes || "-"}</TableCell>
                      </TableRow>
                    );
                  })}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        );
      })}
    </div>
  );
}
