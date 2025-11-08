import { useState } from "react";
import { Card, CardContent } from "./ui/card";
import { Button } from "./ui/button";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "./ui/tabs";
import { TeacherSelector } from "./TeacherSelector";
import { DailyAttendanceTable } from "./DailyAttendanceTable";
import { WeeklySummary } from "./WeeklySummary";
import { WeeklyScheduleView } from "./WeeklyScheduleView";
import { MonthlyReport } from "./MonthlyReport";
import { TeacherManagement } from "./TeacherManagement";
import { FileText, BarChart3, CalendarDays, FileBarChart, Users, LogOut, Shield, BarChart2 } from "lucide-react";
import { Teacher, TeacherAttendance } from "../types/attendance";
import { attendanceData } from "../data/mockData";

interface AdminViewProps {
  teachers: Teacher[];
  onUpdateTeachers: (teachers: Teacher[]) => void;
  onLogout: () => void;
}

export function AdminView({ teachers, onUpdateTeachers, onLogout }: AdminViewProps) {
  const [selectedTeacherId, setSelectedTeacherId] = useState<string | null>(teachers.length > 0 ? teachers[0].id : null);

  const selectedTeacher = teachers.find((t) => t.id === selectedTeacherId);
  const teacherAttendance = attendanceData.find((a) => a.teacherId === selectedTeacherId);

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Admin Header */}
      <div className="bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-lg">
        <div className="container mx-auto px-6 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                <Shield className="w-6 h-6" />
              </div>
              <div>
                <h1 className="text-white text-xl">Panel Administrator</h1>
                <p className="text-emerald-100 text-sm">Sistem Absensi Madrasah Diniyyah</p>
              </div>
            </div>
            <Button onClick={onLogout} variant="outline" className="bg-white/10 border-white/20 text-white hover:bg-white/20 hover:text-white">
              <LogOut className="w-4 h-4 mr-2" />
              Logout
            </Button>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="container mx-auto px-6 py-8">
        <Tabs defaultValue="attendance" className="w-full">
          <TabsList className="grid w-full max-w-3xl grid-cols-4 mb-8">
            <TabsTrigger value="attendance" className="flex items-center gap-2">
              <BarChart2 className="w-4 h-4" />
              Tampilan Admin
            </TabsTrigger>
            <TabsTrigger value="weekly" className="flex items-center gap-2">
              <BarChart3 className="w-4 h-4" />
              Rekapan Mingguan
            </TabsTrigger>
            <TabsTrigger value="monthly" className="flex items-center gap-2">
              <FileBarChart className="w-4 h-4" />
              Rekapan Bulanan
            </TabsTrigger>
            <TabsTrigger value="teachers" className="flex items-center gap-2">
              <Users className="w-4 h-4" />
              Data Pengajar
            </TabsTrigger>
          </TabsList>

          {/* Tampilan Admin Tab */}
          <TabsContent value="attendance">
            <div className="space-y-6">
              <Card>
                <CardContent className="pt-6">
                  <h2 className="text-gray-900 mb-4">Pilih Ustadz/Ustadzah</h2>
                  <TeacherSelector teachers={teachers} selectedTeacherId={selectedTeacherId} onSelectTeacher={setSelectedTeacherId} />
                </CardContent>
              </Card>

              {selectedTeacher && teacherAttendance && (
                <div>
                  <div className="mb-6">
                    <h2 className="text-gray-900">{selectedTeacher.name}</h2>
                    <p className="text-gray-600">{selectedTeacher.subject}</p>
                  </div>

                  <Tabs defaultValue="daily" className="w-full">
                    <TabsList className="grid w-full max-w-2xl grid-cols-2">
                      <TabsTrigger value="daily" className="flex items-center gap-2">
                        <FileText className="w-4 h-4" />
                        Catatan Harian
                      </TabsTrigger>
                      <TabsTrigger value="schedule" className="flex items-center gap-2">
                        <CalendarDays className="w-4 h-4" />
                        Jadwal Mingguan
                      </TabsTrigger>
                    </TabsList>

                    <TabsContent value="daily" className="mt-6">
                      <DailyAttendanceTable dailyRecords={teacherAttendance.records} />
                    </TabsContent>

                    <TabsContent value="schedule" className="mt-6">
                      <WeeklyScheduleView teachers={teachers} />
                    </TabsContent>
                  </Tabs>
                </div>
              )}

              {teachers.length === 0 && (
                <Card>
                  <CardContent className="py-12 text-center text-gray-500">
                    <Users className="w-12 h-12 mx-auto mb-3 opacity-50" />
                    <p>Belum ada data pengajar. Silakan tambahkan di menu Data Pengajar.</p>
                  </CardContent>
                </Card>
              )}
            </div>
          </TabsContent>

          {/* Rekapan Mingguan Tab */}
          <TabsContent value="weekly">
            {selectedTeacher && teacherAttendance ? (
              <div className="space-y-6">
                <Card>
                  <CardContent className="pt-6">
                    <h2 className="text-gray-900 mb-4">Pilih Ustadz/Ustadzah</h2>
                    <TeacherSelector teachers={teachers} selectedTeacherId={selectedTeacherId} onSelectTeacher={setSelectedTeacherId} />
                  </CardContent>
                </Card>

                <div className="mb-6">
                  <h2 className="text-gray-900">{selectedTeacher.name}</h2>
                  <p className="text-gray-600">{selectedTeacher.subject}</p>
                </div>

                <WeeklySummary dailyRecords={teacherAttendance.records} />
              </div>
            ) : (
              <Card>
                <CardContent className="py-12 text-center text-gray-500">
                  <BarChart3 className="w-12 h-12 mx-auto mb-3 opacity-50" />
                  <p>Pilih pengajar untuk melihat rekapan mingguan</p>
                </CardContent>
              </Card>
            )}
          </TabsContent>

          {/* Rekapan Bulanan Tab */}
          <TabsContent value="monthly">
            <MonthlyReport />
          </TabsContent>

          {/* Data Pengajar Tab */}
          <TabsContent value="teachers">
            <TeacherManagement teachers={teachers} onUpdateTeachers={onUpdateTeachers} />
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
}
