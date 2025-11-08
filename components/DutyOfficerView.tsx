import { useState, useEffect } from "react";
import { AttendanceStatus, PrayerTime, DayOfWeek, ClassRoom } from "../types/attendance";
import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Badge } from "./ui/badge";
import { Button } from "./ui/button";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "./ui/table";
import { Avatar, AvatarFallback } from "./ui/avatar";
import { Clock, CheckCircle2, XCircle, AlertCircle, ClipboardCheck, Sunrise, Sun, Sunset, Moon, Calendar, School } from "lucide-react";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";
import { Alert, AlertDescription } from "./ui/alert";
import { getDayNameInIndonesian, classrooms, getClassroomGrade } from "../data/mockData";
import { Input } from "./ui/input";
import { Teacher, ScheduleSlot } from "../types/attendance";

interface DutyOfficerViewProps {
  teachers: Teacher[];
  onMarkAttendance: (teacherId: string, prayerTime: PrayerTime, status: AttendanceStatus) => void;
}

// Helper function to get schedule by day and prayer from teachers data
function getScheduleByDayAndPrayer(teachers: Teacher[], day: DayOfWeek, prayerTime: PrayerTime): ScheduleSlot[] {
  const slots: ScheduleSlot[] = [];

  teachers.forEach((teacher) => {
    if (teacher.schedules) {
      teacher.schedules.forEach((schedule) => {
        if (schedule.day === day && schedule.prayerTime === prayerTime) {
          slots.push({
            day: schedule.day,
            prayerTime: schedule.prayerTime,
            classroom: schedule.classroom,
            teacher: teacher,
            subject: teacher.subject,
          });
        }
      });
    }
  });

  return slots;
}

const prayerTimeInfo: Record<PrayerTime, { label: string; icon: any; color: string; bgColor: string; time: string }> = {
  subuh: { label: "Subuh", icon: Sunrise, color: "text-indigo-600", bgColor: "bg-indigo-100", time: "04:30 - 06:00" },
  ashar: { label: "Ashar", icon: Sun, color: "text-amber-600", bgColor: "bg-amber-100", time: "15:30 - 17:00" },
  maghrib: { label: "Maghrib", icon: Sunset, color: "text-orange-600", bgColor: "bg-orange-100", time: "18:00 - 19:00" },
  isya: { label: "Isya", icon: Moon, color: "text-blue-900", bgColor: "bg-blue-100", time: "19:30 - 21:00" },
};

const dayNames: Record<DayOfWeek, string> = {
  senin: "Senin",
  selasa: "Selasa",
  rabu: "Rabu",
  kamis: "Kamis",
  jumat: "Jumat",
  sabtu: "Sabtu",
  ahad: "Ahad",
};

export function DutyOfficerView({ teachers, onMarkAttendance }: DutyOfficerViewProps) {
  const [currentPrayerTime, setCurrentPrayerTime] = useState<PrayerTime>("subuh");
  const [currentDay, setCurrentDay] = useState<DayOfWeek>("senin");
  const [currentTime, setCurrentTime] = useState(new Date());
  const [attendanceMarks, setAttendanceMarks] = useState<Record<string, AttendanceStatus>>({});
  const [savedMessage, setSavedMessage] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const [filterGrade, setFilterGrade] = useState<string>("all");

  // Update current time every second
  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentTime(new Date());
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  // Auto-detect current day and prayer time based on date/time
  useEffect(() => {
    const now = new Date();
    setCurrentDay(getDayNameInIndonesian(now));

    const hour = now.getHours();
    if (hour >= 4 && hour < 12) {
      setCurrentPrayerTime("subuh");
    } else if (hour >= 12 && hour < 18) {
      setCurrentPrayerTime("ashar");
    } else if (hour >= 18 && hour < 19) {
      setCurrentPrayerTime("maghrib");
    } else {
      setCurrentPrayerTime("isya");
    }
  }, []);

  // Reset attendance marks when day or prayer time changes
  useEffect(() => {
    setAttendanceMarks({});
  }, [currentDay, currentPrayerTime]);

  const formatTime = (date: Date) => {
    return date.toLocaleTimeString("id-ID", {
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
    });
  };

  const formatDate = (date: Date) => {
    return date.toLocaleDateString("id-ID", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    });
  };

  const handleStatusChange = (teacherId: string, status: AttendanceStatus) => {
    setAttendanceMarks((prev) => ({
      ...prev,
      [teacherId]: status,
    }));
  };

  const handleMarkAllPresent = () => {
    const newMarks: Record<string, AttendanceStatus> = {};
    currentSchedule.forEach((schedule) => {
      newMarks[schedule.teacher.id] = "present";
    });
    setAttendanceMarks(newMarks);
  };

  const handleSaveAll = () => {
    Object.entries(attendanceMarks).forEach(([teacherId, status]) => {
      onMarkAttendance(teacherId, currentPrayerTime, status);
    });
    setSavedMessage(true);
    setTimeout(() => setSavedMessage(false), 3000);
  };

  const getStatusColor = (status?: AttendanceStatus) => {
    if (!status) return "bg-gray-100";
    switch (status) {
      case "present":
        return "bg-green-50 border-green-200";
      case "absent":
        return "bg-red-50 border-red-200";
      case "late":
        return "bg-yellow-50 border-yellow-200";
      case "half-day":
        return "bg-orange-50 border-orange-200";
      default:
        return "bg-gray-50";
    }
  };

  const getStatusIcon = (status?: AttendanceStatus) => {
    if (!status) return null;
    switch (status) {
      case "present":
        return <CheckCircle2 className="w-5 h-5 text-green-600" />;
      case "absent":
        return <XCircle className="w-5 h-5 text-red-600" />;
      case "late":
        return <AlertCircle className="w-5 h-5 text-yellow-600" />;
      case "half-day":
        return <ClipboardCheck className="w-5 h-5 text-orange-600" />;
    }
  };

  // Get current schedule based on selected day and prayer time
  const allSchedules = getScheduleByDayAndPrayer(teachers, currentDay, currentPrayerTime);

  // Apply filters
  let currentSchedule = allSchedules;

  // Filter by grade
  if (filterGrade !== "all") {
    currentSchedule = currentSchedule.filter((s) => getClassroomGrade(s.classroom).toString() === filterGrade);
  }

  // Filter by search query
  if (searchQuery) {
    currentSchedule = currentSchedule.filter(
      (s) => s.teacher.name.toLowerCase().includes(searchQuery.toLowerCase()) || s.classroom.toLowerCase().includes(searchQuery.toLowerCase()) || s.subject.toLowerCase().includes(searchQuery.toLowerCase())
    );
  }

  // Sort by classroom
  currentSchedule = [...currentSchedule].sort((a, b) => {
    const gradeA = getClassroomGrade(a.classroom);
    const gradeB = getClassroomGrade(b.classroom);
    if (gradeA !== gradeB) return gradeA - gradeB;
    return a.classroom.localeCompare(b.classroom);
  });

  const markedCount = Object.keys(attendanceMarks).length;
  const unmarkedCount = allSchedules.length - markedCount;

  return (
    <div className="space-y-6">
      {/* Current Time Card */}
      <Card className="bg-gradient-to-r from-emerald-600 to-emerald-700 text-white">
        <CardContent className="pt-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-emerald-100 text-sm mb-1">Waktu Sekarang</p>
              <p className="text-3xl mb-2">{formatTime(currentTime)}</p>
              <p className="text-emerald-100">{formatDate(currentTime)}</p>
            </div>
            <Clock className="w-16 h-16 text-emerald-300" />
          </div>
        </CardContent>
      </Card>

      {/* Day Selection */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Calendar className="w-5 h-5 text-emerald-600" />
            Pilih Hari
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-4 md:grid-cols-7 gap-2">
            {(Object.keys(dayNames) as DayOfWeek[]).map((day) => (
              <Button key={day} variant={currentDay === day ? "default" : "outline"} onClick={() => setCurrentDay(day)} className={currentDay === day ? "bg-emerald-600 hover:bg-emerald-700" : ""}>
                {dayNames[day]}
              </Button>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Prayer Time Selection */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Clock className="w-5 h-5 text-emerald-600" />
            Pilih Waktu Kajian
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
            {(Object.keys(prayerTimeInfo) as PrayerTime[]).map((prayerTime) => {
              const info = prayerTimeInfo[prayerTime];
              const Icon = info.icon;
              const isActive = currentPrayerTime === prayerTime;

              return (
                <Button key={prayerTime} variant={isActive ? "default" : "outline"} onClick={() => setCurrentPrayerTime(prayerTime)} className={`h-auto flex-col gap-2 py-4 ${isActive ? "bg-emerald-600 hover:bg-emerald-700" : ""}`}>
                  <Icon className="w-6 h-6" />
                  <div className="text-center">
                    <div>{info.label}</div>
                    <div className="text-xs opacity-80">{info.time}</div>
                  </div>
                </Button>
              );
            })}
          </div>
        </CardContent>
      </Card>

      {/* Status Summary */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Card>
          <CardContent className="pt-6">
            <div className="text-center">
              <p className="text-2xl text-gray-900 mb-1">{allSchedules.length}</p>
              <p className="text-sm text-gray-600">Total Kelas</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-center">
              <p className="text-2xl text-green-600 mb-1">{markedCount}</p>
              <p className="text-sm text-gray-600">Sudah Dicek</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-center">
              <p className="text-2xl text-gray-600 mb-1">{unmarkedCount}</p>
              <p className="text-sm text-gray-600">Belum Dicek</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-center">
              <div className="flex items-center justify-center gap-2 mb-1">
                <School className="w-6 h-6 text-emerald-600" />
              </div>
              <p className="text-sm text-gray-600">{dayNames[currentDay]}</p>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Success Message */}
      {savedMessage && (
        <Alert className="bg-green-50 border-green-200">
          <CheckCircle2 className="h-4 w-4 text-green-600" />
          <AlertDescription className="text-green-800">
            Data kehadiran berhasil disimpan untuk {dayNames[currentDay]} - {prayerTimeInfo[currentPrayerTime].label}!
          </AlertDescription>
        </Alert>
      )}

      {/* Filters */}
      <Card>
        <CardHeader>
          <CardTitle>Filter & Pencarian</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className="text-sm text-gray-600 mb-2 block">Cari Guru/Kelas</label>
              <Input placeholder="Nama guru atau kelas..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} />
            </div>
            <div>
              <label className="text-sm text-gray-600 mb-2 block">Filter Tingkat</label>
              <Select value={filterGrade} onValueChange={setFilterGrade}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Semua Tingkat</SelectItem>
                  <SelectItem value="7">Kelas 7</SelectItem>
                  <SelectItem value="8">Kelas 8</SelectItem>
                  <SelectItem value="9">Kelas 9</SelectItem>
                  <SelectItem value="10">Kelas 10</SelectItem>
                  <SelectItem value="11">Kelas 11</SelectItem>
                  <SelectItem value="12">Kelas 12</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="flex items-end">
              <Button onClick={handleMarkAllPresent} variant="outline" className="w-full">
                <CheckCircle2 className="w-4 h-4 mr-2" />
                Tandai Semua Hadir
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Attendance Table */}
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <div>
              <CardTitle>
                Daftar Kehadiran - {dayNames[currentDay]}, {prayerTimeInfo[currentPrayerTime].label}
              </CardTitle>
              <p className="text-sm text-gray-600 mt-1">
                {prayerTimeInfo[currentPrayerTime].time} ��� {currentSchedule.length} dari {allSchedules.length} kelas ditampilkan
              </p>
            </div>
            <Button onClick={handleSaveAll} disabled={markedCount === 0} className="bg-emerald-600 hover:bg-emerald-700">
              <CheckCircle2 className="w-4 h-4 mr-2" />
              Simpan Semua ({markedCount})
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          {currentSchedule.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              <School className="w-12 h-12 mx-auto mb-3 opacity-50" />
              <p>Tidak ada jadwal yang sesuai dengan filter</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead className="w-12">#</TableHead>
                    <TableHead>Kelas</TableHead>
                    <TableHead>Nama Ustadz/Ustadzah</TableHead>
                    <TableHead>Mata Pelajaran</TableHead>
                    <TableHead>Status Kehadiran</TableHead>
                    <TableHead className="w-32">Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {currentSchedule.map((schedule, index) => {
                    const teacher = schedule.teacher;
                    const initials = teacher.name
                      .split(" ")
                      .slice(0, 2)
                      .map((n) => n[0])
                      .join("");
                    const status = attendanceMarks[teacher.id];

                    return (
                      <TableRow key={`${schedule.classroom}-${teacher.id}`} className={`${getStatusColor(status)} transition-colors`}>
                        <TableCell>{index + 1}</TableCell>
                        <TableCell>
                          <Badge variant="outline" className="bg-blue-50 text-blue-700 border-blue-200">
                            {schedule.classroom}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <div className="flex items-center gap-3">
                            <Avatar className="w-8 h-8">
                              <AvatarFallback className="text-xs bg-emerald-600 text-white">{initials}</AvatarFallback>
                            </Avatar>
                            <span>{teacher.name}</span>
                          </div>
                        </TableCell>
                        <TableCell>{schedule.subject}</TableCell>
                        <TableCell>
                          <Select value={status || ""} onValueChange={(value) => handleStatusChange(teacher.id, value as AttendanceStatus)}>
                            <SelectTrigger className="w-full">
                              <SelectValue placeholder="Pilih status" />
                            </SelectTrigger>
                            <SelectContent>
                              <SelectItem value="present">
                                <div className="flex items-center gap-2">
                                  <CheckCircle2 className="w-4 h-4 text-green-600" />
                                  Hadir
                                </div>
                              </SelectItem>
                              <SelectItem value="late">
                                <div className="flex items-center gap-2">
                                  <AlertCircle className="w-4 h-4 text-yellow-600" />
                                  Terlambat
                                </div>
                              </SelectItem>
                              <SelectItem value="absent">
                                <div className="flex items-center gap-2">
                                  <XCircle className="w-4 h-4 text-red-600" />
                                  Tidak Hadir
                                </div>
                              </SelectItem>
                              <SelectItem value="half-day">
                                <div className="flex items-center gap-2">
                                  <ClipboardCheck className="w-4 h-4 text-orange-600" />
                                  Izin
                                </div>
                              </SelectItem>
                            </SelectContent>
                          </Select>
                        </TableCell>
                        <TableCell>
                          <div className="flex items-center justify-center">
                            {status ? (
                              <div className="flex items-center gap-2">{getStatusIcon(status)}</div>
                            ) : (
                              <Badge variant="outline" className="bg-gray-100">
                                Belum dicek
                              </Badge>
                            )}
                          </div>
                        </TableCell>
                      </TableRow>
                    );
                  })}
                </TableBody>
              </Table>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Legend */}
      <Card>
        <CardHeader>
          <CardTitle>Keterangan Status</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div className="flex items-center gap-2">
              <CheckCircle2 className="w-5 h-5 text-green-600" />
              <span className="text-sm">Hadir - Tepat waktu</span>
            </div>
            <div className="flex items-center gap-2">
              <AlertCircle className="w-5 h-5 text-yellow-600" />
              <span className="text-sm">Terlambat - Datang terlambat</span>
            </div>
            <div className="flex items-center gap-2">
              <XCircle className="w-5 h-5 text-red-600" />
              <span className="text-sm">Tidak Hadir - Tidak hadir</span>
            </div>
            <div className="flex items-center gap-2">
              <ClipboardCheck className="w-5 h-5 text-orange-600" />
              <span className="text-sm">Izin - Izin/sakit</span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
