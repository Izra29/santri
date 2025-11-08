import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Badge } from "./ui/badge";
import { Sunrise, Sun, Sunset, Moon, Calendar, School } from "lucide-react";
import { classrooms, getClassroomGrade } from "../data/mockData";
import { DayOfWeek, PrayerTime, ClassRoom, Teacher, ScheduleSlot } from "../types/attendance";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "./ui/tabs";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";

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

const prayerTimeInfo: Record<PrayerTime, { label: string; icon: any; color: string }> = {
  subuh: { label: "Subuh", icon: Sunrise, color: "text-indigo-600" },
  ashar: { label: "Ashar", icon: Sun, color: "text-amber-600" },
  maghrib: { label: "Maghrib", icon: Sunset, color: "text-orange-600" },
  isya: { label: "Isya", icon: Moon, color: "text-blue-900" },
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

const daysOrder: DayOfWeek[] = ["senin", "selasa", "rabu", "kamis", "jumat", "sabtu", "ahad"];
const prayerOrder: PrayerTime[] = ["subuh", "ashar", "maghrib", "isya"];

interface WeeklyScheduleViewProps {
  teachers: Teacher[];
}

export function WeeklyScheduleView({ teachers }: WeeklyScheduleViewProps) {
  const [selectedDay, setSelectedDay] = useState<DayOfWeek>("senin");
  const [selectedPrayer, setSelectedPrayer] = useState<PrayerTime>("subuh");

  const currentSchedule = getScheduleByDayAndPrayer(teachers, selectedDay, selectedPrayer);

  // Group by grade
  const scheduleByGrade: Record<string, typeof currentSchedule> = {
    "7": [],
    "8": [],
    "9": [],
    "10": [],
    "11": [],
    "12": [],
  };

  currentSchedule.forEach((schedule) => {
    const grade = getClassroomGrade(schedule.classroom).toString();
    scheduleByGrade[grade].push(schedule);
  });

  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Calendar className="w-5 h-5 text-emerald-600" />
            Jadwal Lengkap Mingguan
          </CardTitle>
        </CardHeader>
        <CardContent>
          <Tabs value={selectedDay} onValueChange={(value) => setSelectedDay(value as DayOfWeek)}>
            <TabsList className="grid w-full grid-cols-7">
              {daysOrder.map((day) => (
                <TabsTrigger key={day} value={day}>
                  {dayNames[day]}
                </TabsTrigger>
              ))}
            </TabsList>

            {daysOrder.map((day) => (
              <TabsContent key={day} value={day} className="mt-6">
                <div className="space-y-4">
                  <div className="flex items-center gap-4 mb-4">
                    <label className="text-sm text-gray-600">Pilih Waktu:</label>
                    <Select value={selectedPrayer} onValueChange={(value) => setSelectedPrayer(value as PrayerTime)}>
                      <SelectTrigger className="w-48">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        {prayerOrder.map((prayer) => {
                          const info = prayerTimeInfo[prayer];
                          const Icon = info.icon;
                          return (
                            <SelectItem key={prayer} value={prayer}>
                              <div className="flex items-center gap-2">
                                <Icon className={`w-4 h-4 ${info.color}`} />
                                {info.label}
                              </div>
                            </SelectItem>
                          );
                        })}
                      </SelectContent>
                    </Select>
                  </div>

                  <div className="grid grid-cols-1 gap-6">
                    {Object.keys(scheduleByGrade).map((grade) => {
                      const gradeSchedules = scheduleByGrade[grade];
                      if (gradeSchedules.length === 0) return null;

                      return (
                        <Card key={grade}>
                          <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                              <School className="w-5 h-5 text-blue-600" />
                              Kelas {grade}
                            </CardTitle>
                          </CardHeader>
                          <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                              {gradeSchedules
                                .sort((a, b) => a.classroom.localeCompare(b.classroom))
                                .map((schedule) => (
                                  <Card key={schedule.classroom} className="border-2">
                                    <CardContent className="pt-4">
                                      <div className="space-y-2">
                                        <div className="flex items-center justify-between">
                                          <Badge className="bg-blue-600">Kelas {schedule.classroom}</Badge>
                                        </div>
                                        <div>
                                          <p className="text-sm text-gray-900">{schedule.teacher.name}</p>
                                          <p className="text-xs text-gray-600">{schedule.subject}</p>
                                        </div>
                                      </div>
                                    </CardContent>
                                  </Card>
                                ))}
                            </div>
                          </CardContent>
                        </Card>
                      );
                    })}
                  </div>
                </div>
              </TabsContent>
            ))}
          </Tabs>
        </CardContent>
      </Card>
    </div>
  );
}
