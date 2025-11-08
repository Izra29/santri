import { Teacher, TeacherAttendance, AttendanceStatus, PrayerTime, ScheduleSlot, DayOfWeek, ClassRoom } from "../types/attendance";

// Daftar semua kelas
export const classrooms: ClassRoom[] = ["7A", "7B", "7C", "7D", "8A", "8B", "8C", "8D", "9A", "9B", "9C", "9D", "10A", "10B", "11", "12"];

// Semua Ustadz dan Ustadzah (butuh banyak guru karena 16 kelas x berbagai waktu)
export const allTeachers: Teacher[] = [
  // Pengajar untuk berbagai kelas dan waktu
  { id: "t1", name: "Ustadz Ahmad Fauzi", subject: "Tahfidz Quran" },
  { id: "t2", name: "Ustadzah Fatimah Az-Zahra", subject: "Tahsin & Tajwid" },
  { id: "t3", name: "Ustadz Muhammad Rizki", subject: "Quran Hadits" },
  { id: "t4", name: "Ustadz Azis Abdullah", subject: "Tahfidz Quran" },
  { id: "t5", name: "Ustadzah Maryam Husna", subject: "Tahsin & Tajwid" },
  { id: "t6", name: "Ustadz Abdullah Hasan", subject: "Fiqih" },
  { id: "t7", name: "Ustadzah Khadijah Rahman", subject: "Akidah Akhlak" },
  { id: "t8", name: "Ustadz Yusuf Ibrahim", subject: "Bahasa Arab" },
  { id: "t9", name: "Ustadzah Aisyah Nur", subject: "Sejarah Islam" },
  { id: "t10", name: "Ustadz Fahri Ramadhan", subject: "Fiqih" },
  { id: "t11", name: "Ustadzah Halimah Saadiah", subject: "Akidah Akhlak" },
  { id: "t12", name: "Ustadz Umar Faruq", subject: "Tafsir Quran" },
  { id: "t13", name: "Ustadzah Zainab Husna", subject: "Hadits" },
  { id: "t14", name: "Ustadz Bilal Hasyim", subject: "Sirah Nabawiyah" },
  { id: "t15", name: "Ustadz Khalid Walid", subject: "Tafsir Quran" },
  { id: "t16", name: "Ustadz Salman Alfarisi", subject: "Ushul Fiqh" },
  { id: "t17", name: "Ustadzah Hafshah Karimah", subject: "Kajian Kitab Kuning" },
  { id: "t18", name: "Ustadz Hamzah Malik", subject: "Tauhid" },
  { id: "t19", name: "Ustadzah Ruqayyah Siti", subject: "Adab & Tasawuf" },
  { id: "t20", name: "Ustadz Zaid Haris", subject: "Ushul Fiqh" },
  { id: "t21", name: "Ustadz Ibrahim Khalil", subject: "Tahfidz Quran" },
  { id: "t22", name: "Ustadzah Asma Rahmah", subject: "Tahsin & Tajwid" },
  { id: "t23", name: "Ustadz Hasan Basri", subject: "Fiqih" },
  { id: "t24", name: "Ustadzah Sumayah Latif", subject: "Akidah Akhlak" },
  { id: "t25", name: "Ustadz Anwar Said", subject: "Bahasa Arab" },
  { id: "t26", name: "Ustadzah Layla Zahra", subject: "Quran Hadits" },
  { id: "t27", name: "Ustadz Malik Ismail", subject: "Tafsir Quran" },
  { id: "t28", name: "Ustadzah Nadia Huda", subject: "Hadits" },
  { id: "t29", name: "Ustadz Rashid Amin", subject: "Sirah Nabawiyah" },
  { id: "t30", name: "Ustadzah Safiya Iman", subject: "Tauhid" },
  { id: "t31", name: "Ustadz Tariq Nasir", subject: "Ushul Fiqh" },
  { id: "t32", name: "Ustadzah Yasmin Noor", subject: "Adab & Tasawuf" },
  { id: "t33", name: "Ustadz Waleed Hassan", subject: "Kajian Kitab Kuning" },
  { id: "t34", name: "Ustadzah Zaynab Khatib", subject: "Sejarah Islam" },
  { id: "t35", name: "Ustadz Qasim Jafar", subject: "Tahfidz Quran" },
  { id: "t36", name: "Ustadzah Rabia Amina", subject: "Tahsin & Tajwid" },
  { id: "t37", name: "Ustadz Samir Lutfi", subject: "Fiqih" },
  { id: "t38", name: "Ustadzah Dina Salma", subject: "Akidah Akhlak" },
  { id: "t39", name: "Ustadz Nasir Rahman", subject: "Bahasa Arab" },
  { id: "t40", name: "Ustadzah Hiba Qadir", subject: "Quran Hadits" },
  { id: "t41", name: "Ustadz Munir Hakim", subject: "Tafsir Quran" },
  { id: "t42", name: "Ustadzah Lina Farida", subject: "Hadits" },
  { id: "t43", name: "Ustadz Jamal Bakri", subject: "Sirah Nabawiyah" },
  { id: "t44", name: "Ustadzah Marwa Salam", subject: "Tauhid" },
  { id: "t45", name: "Ustadz Kamal Faris", subject: "Ushul Fiqh" },
  { id: "t46", name: "Ustadzah Rania Habib", subject: "Adab & Tasawuf" },
  { id: "t47", name: "Ustadz Imran Zahir", subject: "Kajian Kitab Kuning" },
  { id: "t48", name: "Ustadzah Amira Najwa", subject: "Sejarah Islam" },
];

// Daftar mata pelajaran berdasarkan waktu
const subjectsByPrayer: Record<PrayerTime, string[]> = {
  subuh: ["Tahfidz Quran", "Tahsin & Tajwid", "Quran Hadits"],
  ashar: ["Fiqih", "Akidah Akhlak", "Bahasa Arab", "Sejarah Islam"],
  maghrib: ["Tafsir Quran", "Hadits", "Sirah Nabawiyah"],
  isya: ["Ushul Fiqh", "Kajian Kitab Kuning", "Tauhid", "Adab & Tasawuf"],
};

// Generate jadwal lengkap - setiap hari, setiap waktu, 16 kelas
// Assign schedules to teachers automatically for demo purposes
const days: DayOfWeek[] = ["senin", "selasa", "rabu", "kamis", "jumat", "sabtu", "ahad"];
const prayers: PrayerTime[] = ["subuh", "ashar", "maghrib", "isya"];

let teacherIndex = 0;
days.forEach((day) => {
  prayers.forEach((prayer) => {
    classrooms.forEach((classroom, index) => {
      const teacher = allTeachers[teacherIndex % allTeachers.length];

      // Assign schedule to teacher
      if (!teacher.schedules) {
        teacher.schedules = [];
      }

      // Check if this schedule doesn't already exist
      const exists = teacher.schedules.some((s) => s.day === day && s.prayerTime === prayer && s.classroom === classroom);

      if (!exists) {
        teacher.schedules.push({
          day,
          prayerTime: prayer,
          classroom,
        });
      }

      teacherIndex++;
    });
  });
});

// Fungsi helper untuk mendapatkan semua jadwal guru tertentu
export function getTeacherSchedule(teacherId: string) {
  const teacher = allTeachers.find((t) => t.id === teacherId);
  return teacher?.schedules || [];
}

// Convert day name from Date to Indonesian
export function getDayNameInIndonesian(date: Date): DayOfWeek {
  const days: DayOfWeek[] = ["ahad", "senin", "selasa", "rabu", "kamis", "jumat", "sabtu"];
  return days[date.getDay()];
}

// Get classroom grade level
export function getClassroomGrade(classroom: ClassRoom): number {
  return parseInt(classroom.replace(/[A-D]/g, ""));
}

// Generate mock attendance data for the current week
const generateMockAttendance = (teacherId: string, daysBack: number = 7): TeacherAttendance => {
  const records = [];
  const statuses: AttendanceStatus[] = ["present", "present", "present", "present", "late", "absent", "half-day"];

  // Get all schedules for this teacher
  const teacherSchedules = getTeacherSchedule(teacherId);

  for (let i = daysBack - 1; i >= 0; i--) {
    const date = new Date();
    date.setDate(date.getDate() - i);
    const dateString = date.toISOString().split("T")[0];
    const dayName = getDayNameInIndonesian(date);

    // Get schedules for this specific day for this teacher
    const daySchedules = teacherSchedules.filter((s) => s.day === dayName);

    // Get unique prayer times for this day
    const uniquePrayerTimes = Array.from(new Set(daySchedules.map((s) => s.prayerTime)));

    const prayers = uniquePrayerTimes.map((prayerTime) => {
      const statusIndex = Math.floor(Math.random() * statuses.length);
      return {
        prayerTime,
        status: statuses[statusIndex],
        notes: statusIndex === 5 ? "Izin sakit" : undefined,
      };
    });

    if (prayers.length > 0) {
      records.push({ date: dateString, prayers });
    }
  }

  return { teacherId, records };
};

export const attendanceData: TeacherAttendance[] = allTeachers.map((teacher) => generateMockAttendance(teacher.id));
