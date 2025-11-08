import { Teacher, PrayerTime } from "../types/attendance";
import { Card } from "./ui/card";
import { Avatar, AvatarFallback } from "./ui/avatar";
import { CheckCircle2, Sunrise, Sun, Sunset, Moon } from "lucide-react";
import { Badge } from "./ui/badge";
import { getTeacherSchedule } from "../data/mockData";

interface TeacherSelectorProps {
  teachers: Teacher[];
  selectedTeacherId: string | null;
  onSelectTeacher: (teacherId: string) => void;
}

const prayerTimeColors: Record<PrayerTime, { bg: string; border: string; text: string; icon: any; label: string }> = {
  subuh: { bg: "bg-indigo-50", border: "border-indigo-200", text: "text-indigo-700", icon: Sunrise, label: "Subuh" },
  ashar: { bg: "bg-amber-50", border: "border-amber-200", text: "text-amber-700", icon: Sun, label: "Ashar" },
  maghrib: { bg: "bg-orange-50", border: "border-orange-200", text: "text-orange-700", icon: Sunset, label: "Maghrib" },
  isya: { bg: "bg-blue-50", border: "border-blue-200", text: "text-blue-700", icon: Moon, label: "Isya" },
};

export function TeacherSelector({ teachers, selectedTeacherId, onSelectTeacher }: TeacherSelectorProps) {
  // Group teachers by their primary prayer time (most frequent)
  const teachersByPrayer: Record<PrayerTime, Teacher[]> = {
    subuh: [],
    ashar: [],
    maghrib: [],
    isya: [],
  };

  teachers.forEach((teacher) => {
    const schedule = getTeacherSchedule(teacher.id);
    if (schedule.length > 0) {
      // Count prayer times
      const prayerCounts: Record<PrayerTime, number> = {
        subuh: 0,
        ashar: 0,
        maghrib: 0,
        isya: 0,
      };

      schedule.forEach((s) => {
        prayerCounts[s.prayerTime]++;
      });

      // Get the most frequent prayer time
      let maxPrayer: PrayerTime = "subuh";
      let maxCount = 0;
      (Object.keys(prayerCounts) as PrayerTime[]).forEach((prayer) => {
        if (prayerCounts[prayer] > maxCount) {
          maxCount = prayerCounts[prayer];
          maxPrayer = prayer;
        }
      });

      teachersByPrayer[maxPrayer].push(teacher);
    }
  });

  return (
    <div className="space-y-6">
      {(Object.keys(prayerTimeColors) as PrayerTime[]).map((prayerTime) => {
        const teachersInTime = teachersByPrayer[prayerTime];
        if (teachersInTime.length === 0) return null;

        const config = prayerTimeColors[prayerTime];
        const Icon = config.icon;

        return (
          <div key={prayerTime}>
            <div className="flex items-center gap-2 mb-3">
              <Icon className={`w-5 h-5 ${config.text}`} />
              <h3 className="text-gray-900">Pengajar Waktu {config.label}</h3>
              <Badge variant="outline" className={`${config.bg} ${config.border} ${config.text}`}>
                {teachersInTime.length} pengajar
              </Badge>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
              {teachersInTime.map((teacher) => {
                const isSelected = selectedTeacherId === teacher.id;
                const initials = teacher.name
                  .split(" ")
                  .slice(0, 2)
                  .map((n) => n[0])
                  .join("");

                return (
                  <Card key={teacher.id} className={`p-4 cursor-pointer transition-all hover:shadow-md border ${isSelected ? `ring-2 ring-emerald-600 ${config.bg}` : `${config.border}`}`} onClick={() => onSelectTeacher(teacher.id)}>
                    <div className="flex items-start gap-3">
                      <Avatar>
                        <AvatarFallback className={isSelected ? "bg-emerald-600 text-white" : `${config.bg} ${config.text}`}>{initials}</AvatarFallback>
                      </Avatar>
                      <div className="flex-1 min-w-0">
                        <div className="flex items-start justify-between gap-2">
                          <p className={`truncate ${isSelected ? "text-emerald-900" : "text-gray-900"}`}>{teacher.name}</p>
                          {isSelected && <CheckCircle2 className="w-5 h-5 text-emerald-600 flex-shrink-0" />}
                        </div>
                        <p className="text-sm text-gray-600 truncate">{teacher.subject}</p>
                      </div>
                    </div>
                  </Card>
                );
              })}
            </div>
          </div>
        );
      })}
    </div>
  );
}
