export type AttendanceStatus = "present" | "absent" | "late" | "half-day";
export type PrayerTime = "subuh" | "ashar" | "maghrib" | "isya";
export type DayOfWeek = "senin" | "selasa" | "rabu" | "kamis" | "jumat" | "sabtu" | "ahad";
export type ClassRoom = "7A" | "7B" | "7C" | "7D" | "8A" | "8B" | "8C" | "8D" | "9A" | "9B" | "9C" | "9D" | "10A" | "10B" | "11" | "12";

export interface TeacherScheduleAssignment {
  day: DayOfWeek;
  prayerTime: PrayerTime;
  classroom: ClassRoom;
}

export interface Teacher {
  id: string;
  name: string;
  subject: string;
  schedules?: TeacherScheduleAssignment[];
}

export interface ScheduleSlot {
  day: DayOfWeek;
  prayerTime: PrayerTime;
  classroom: ClassRoom;
  teacher: Teacher;
  subject: string;
}

export interface PrayerTimeSchedule {
  prayerTime: PrayerTime;
  teacher: Teacher;
  subject: string;
}

export interface PrayerAttendance {
  prayerTime: PrayerTime;
  status: AttendanceStatus;
  notes?: string;
}

export interface DailyAttendance {
  date: string;
  prayers: PrayerAttendance[];
}

export interface TeacherAttendance {
  teacherId: string;
  records: DailyAttendance[];
}
