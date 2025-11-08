import { DailyAttendance, PrayerTime } from "../types/attendance";
import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Badge } from "./ui/badge";
import { TrendingUp, Calendar, AlertCircle, Sunrise, Sun, Sunset, Moon } from "lucide-react";

interface WeeklySummaryProps {
  dailyRecords: DailyAttendance[];
}

const prayerTimeInfo: Record<PrayerTime, { label: string; icon: any; color: string }> = {
  subuh: { label: "Subuh", icon: Sunrise, color: "text-indigo-600" },
  ashar: { label: "Ashar", icon: Sun, color: "text-amber-600" },
  maghrib: { label: "Maghrib", icon: Sunset, color: "text-orange-600" },
  isya: { label: "Isya", icon: Moon, color: "text-blue-900" },
};

export function WeeklySummary({ dailyRecords }: WeeklySummaryProps) {
  const calculateStats = () => {
    let totalSessions = 0;
    let presentCount = 0;
    let absentCount = 0;
    let lateCount = 0;
    let izinCount = 0;

    const prayerStats: Record<PrayerTime, { present: number; total: number }> = {
      subuh: { present: 0, total: 0 },
      ashar: { present: 0, total: 0 },
      maghrib: { present: 0, total: 0 },
      isya: { present: 0, total: 0 },
    };

    dailyRecords.forEach((record) => {
      record.prayers.forEach((prayer) => {
        totalSessions++;
        prayerStats[prayer.prayerTime].total++;

        if (prayer.status === "present") {
          presentCount++;
          prayerStats[prayer.prayerTime].present++;
        }
        if (prayer.status === "absent") absentCount++;
        if (prayer.status === "late") lateCount++;
        if (prayer.status === "half-day") izinCount++;
      });
    });

    const attendanceRate = totalSessions > 0 ? ((presentCount / totalSessions) * 100).toFixed(1) : "0";

    return {
      totalSessions,
      presentCount,
      absentCount,
      lateCount,
      izinCount,
      attendanceRate,
      daysTracked: dailyRecords.length,
      prayerStats,
    };
  };

  const stats = calculateStats();

  const statCards = [
    {
      title: "Tingkat Kehadiran",
      value: `${stats.attendanceRate}%`,
      icon: TrendingUp,
      color: "text-green-600",
      bgColor: "bg-green-100",
    },
    {
      title: "Hadir",
      value: stats.presentCount.toString(),
      icon: Calendar,
      color: "text-blue-600",
      bgColor: "bg-blue-100",
    },
    {
      title: "Tidak Hadir",
      value: stats.absentCount.toString(),
      icon: AlertCircle,
      color: "text-red-600",
      bgColor: "bg-red-100",
    },
    {
      title: "Terlambat",
      value: stats.lateCount.toString(),
      icon: AlertCircle,
      color: "text-yellow-600",
      bgColor: "bg-yellow-100",
    },
  ];

  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Ringkasan Mingguan</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {statCards.map((stat) => {
              const Icon = stat.icon;
              return (
                <div key={stat.title} className="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                  <div className={`${stat.bgColor} p-3 rounded-lg`}>
                    <Icon className={`w-5 h-5 ${stat.color}`} />
                  </div>
                  <div>
                    <p className="text-sm text-gray-600">{stat.title}</p>
                    <p className="text-gray-900 mt-1">{stat.value}</p>
                  </div>
                </div>
              );
            })}
          </div>
        </CardContent>
      </Card>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Rincian Kehadiran</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-gray-700">Total Hari Tercatat</span>
                <Badge variant="outline">{stats.daysTracked} hari</Badge>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-gray-700">Total Sesi Kajian</span>
                <Badge variant="outline">{stats.totalSessions} sesi</Badge>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-gray-700">Hadir</span>
                <Badge className="bg-green-100 text-green-800 hover:bg-green-100">{stats.presentCount} sesi</Badge>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-gray-700">Tidak Hadir</span>
                <Badge className="bg-red-100 text-red-800 hover:bg-red-100">{stats.absentCount} sesi</Badge>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-gray-700">Terlambat</span>
                <Badge className="bg-yellow-100 text-yellow-800 hover:bg-yellow-100">{stats.lateCount} sesi</Badge>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-gray-700">Izin</span>
                <Badge className="bg-orange-100 text-orange-800 hover:bg-orange-100">{stats.izinCount} sesi</Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Kehadiran Per Waktu</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {(Object.keys(prayerTimeInfo) as PrayerTime[]).map((prayerTime) => {
                const info = prayerTimeInfo[prayerTime];
                const Icon = info.icon;
                const prayerStat = stats.prayerStats[prayerTime];
                const rate = prayerStat.total > 0 ? ((prayerStat.present / prayerStat.total) * 100).toFixed(0) : "0";

                return (
                  <div key={prayerTime} className="space-y-2">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <Icon className={`w-5 h-5 ${info.color}`} />
                        <span className="text-gray-700">{info.label}</span>
                      </div>
                      <span className="text-sm text-gray-600">
                        {prayerStat.present}/{prayerStat.total} ({rate}%)
                      </span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div className="bg-emerald-600 h-2 rounded-full transition-all" style={{ width: `${rate}%` }} />
                    </div>
                  </div>
                );
              })}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
