import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { Label } from "./ui/label";
import { Badge } from "./ui/badge";
import { Avatar, AvatarFallback } from "./ui/avatar";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "./ui/table";
import { Checkbox } from "./ui/checkbox";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "./ui/dialog";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "./ui/alert-dialog";
import { Alert, AlertDescription } from "./ui/alert";
import { Plus, Edit, Trash2, Search, UserPlus, CheckCircle2, Users, Calendar, Clock, School } from "lucide-react";
import { Teacher, DayOfWeek, PrayerTime, ClassRoom, TeacherScheduleAssignment } from "../types/attendance";
import { classrooms } from "../data/mockData";

interface TeacherManagementProps {
  teachers: Teacher[];
  onUpdateTeachers: (teachers: Teacher[]) => void;
}

const dayNames: Record<DayOfWeek, string> = {
  senin: "Senin",
  selasa: "Selasa",
  rabu: "Rabu",
  kamis: "Kamis",
  jumat: "Jumat",
  sabtu: "Sabtu",
  ahad: "Ahad",
};

const prayerTimeNames: Record<PrayerTime, string> = {
  subuh: "Subuh",
  ashar: "Ashar",
  maghrib: "Maghrib",
  isya: "Isya",
};

const allDays: DayOfWeek[] = ["senin", "selasa", "rabu", "kamis", "jumat", "sabtu", "ahad"];
const allPrayerTimes: PrayerTime[] = ["subuh", "ashar", "maghrib", "isya"];

export function TeacherManagement({ teachers, onUpdateTeachers }: TeacherManagementProps) {
  const [searchQuery, setSearchQuery] = useState("");
  const [isAddDialogOpen, setIsAddDialogOpen] = useState(false);
  const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
  const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
  const [selectedTeacher, setSelectedTeacher] = useState<Teacher | null>(null);
  const [successMessage, setSuccessMessage] = useState("");

  // Form state
  const [formData, setFormData] = useState({
    name: "",
    subject: "",
  });

  // Schedule state
  const [selectedSchedules, setSelectedSchedules] = useState<TeacherScheduleAssignment[]>([]);

  const filteredTeachers = teachers.filter((teacher) => teacher.name.toLowerCase().includes(searchQuery.toLowerCase()) || teacher.subject.toLowerCase().includes(searchQuery.toLowerCase()));

  const resetForm = () => {
    setFormData({ name: "", subject: "" });
    setSelectedSchedules([]);
  };

  const showSuccess = (message: string) => {
    setSuccessMessage(message);
    setTimeout(() => setSuccessMessage(""), 3000);
  };

  const toggleSchedule = (day: DayOfWeek, prayerTime: PrayerTime, classroom: ClassRoom) => {
    const scheduleKey = `${day}-${prayerTime}-${classroom}`;
    const exists = selectedSchedules.some((s) => s.day === day && s.prayerTime === prayerTime && s.classroom === classroom);

    if (exists) {
      setSelectedSchedules(selectedSchedules.filter((s) => !(s.day === day && s.prayerTime === prayerTime && s.classroom === classroom)));
    } else {
      setSelectedSchedules([...selectedSchedules, { day, prayerTime, classroom }]);
    }
  };

  const isScheduleSelected = (day: DayOfWeek, prayerTime: PrayerTime, classroom: ClassRoom) => {
    return selectedSchedules.some((s) => s.day === day && s.prayerTime === prayerTime && s.classroom === classroom);
  };

  const handleAdd = () => {
    const newTeacher: Teacher = {
      id: `t${Date.now()}`,
      name: formData.name,
      subject: formData.subject,
      schedules: selectedSchedules,
    };

    onUpdateTeachers([...teachers, newTeacher]);
    setIsAddDialogOpen(false);
    resetForm();
    showSuccess("Pengajar berhasil ditambahkan!");
  };

  const handleEdit = () => {
    if (!selectedTeacher) return;

    const updatedTeachers = teachers.map((teacher) => (teacher.id === selectedTeacher.id ? { ...teacher, ...formData, schedules: selectedSchedules } : teacher));

    onUpdateTeachers(updatedTeachers);
    setIsEditDialogOpen(false);
    setSelectedTeacher(null);
    resetForm();
    showSuccess("Data pengajar berhasil diperbarui!");
  };

  const handleDelete = () => {
    if (!selectedTeacher) return;

    const updatedTeachers = teachers.filter((teacher) => teacher.id !== selectedTeacher.id);
    onUpdateTeachers(updatedTeachers);
    setIsDeleteDialogOpen(false);
    setSelectedTeacher(null);
    showSuccess("Pengajar berhasil dihapus!");
  };

  const openEditDialog = (teacher: Teacher) => {
    setSelectedTeacher(teacher);
    setFormData({
      name: teacher.name,
      subject: teacher.subject,
    });
    setSelectedSchedules(teacher.schedules || []);
    setIsEditDialogOpen(true);
  };

  const openDeleteDialog = (teacher: Teacher) => {
    setSelectedTeacher(teacher);
    setIsDeleteDialogOpen(true);
  };

  const openAddDialog = () => {
    resetForm();
    setIsAddDialogOpen(true);
  };

  const ScheduleSelector = () => (
    <div className="space-y-4">
      <div className="flex items-center gap-2 text-sm text-gray-600">
        <Calendar className="w-4 h-4" />
        <span>Pilih Jadwal Mengajar (Hari, Waktu, dan Kelas)</span>
      </div>

      {allDays.map((day) => (
        <div key={day} className="border rounded-lg p-4">
          <h4 className="font-medium mb-3">{dayNames[day]}</h4>
          <div className="space-y-3">
            {allPrayerTimes.map((prayer) => (
              <div key={prayer} className="border-l-2 border-gray-200 pl-3">
                <div className="flex items-center gap-2 mb-2">
                  <Clock className="w-4 h-4 text-gray-500" />
                  <span className="text-sm font-medium text-gray-700">{prayerTimeNames[prayer]}</span>
                </div>
                <div className="grid grid-cols-4 gap-2">
                  {classrooms.map((classroom) => {
                    const isSelected = isScheduleSelected(day, prayer, classroom);
                    return (
                      <div
                        key={classroom}
                        className={`flex items-center gap-2 p-2 rounded border cursor-pointer transition-colors ${isSelected ? "bg-emerald-50 border-emerald-500" : "bg-white border-gray-200 hover:bg-gray-50"}`}
                        onClick={() => toggleSchedule(day, prayer, classroom)}
                      >
                        <Checkbox checked={isSelected} onCheckedChange={() => toggleSchedule(day, prayer, classroom)} />
                        <span className="text-sm">{classroom}</span>
                      </div>
                    );
                  })}
                </div>
              </div>
            ))}
          </div>
        </div>
      ))}

      <div className="bg-blue-50 border border-blue-200 rounded-lg p-3">
        <div className="flex items-center gap-2 text-sm text-blue-800">
          <School className="w-4 h-4" />
          <span>
            Total jadwal dipilih: <strong>{selectedSchedules.length}</strong> kelas
          </span>
        </div>
      </div>
    </div>
  );

  return (
    <div className="space-y-6">
      {/* Header */}
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <div>
              <CardTitle className="flex items-center gap-2">
                <Users className="w-5 h-5 text-emerald-600" />
                Manajemen Data Pengajar
              </CardTitle>
              <p className="text-sm text-gray-600 mt-1">Kelola data ustadz dan ustadzah</p>
            </div>
            <Button onClick={openAddDialog} className="bg-emerald-600 hover:bg-emerald-700">
              <Plus className="w-4 h-4 mr-2" />
              Tambah Pengajar
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <div className="flex items-center gap-4">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
              <Input placeholder="Cari nama atau mata pelajaran..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="pl-10" />
            </div>
            <Badge variant="outline" className="px-4 py-2">
              Total: {teachers.length} Pengajar
            </Badge>
          </div>
        </CardContent>
      </Card>

      {/* Success Message */}
      {successMessage && (
        <Alert className="bg-green-50 border-green-200">
          <CheckCircle2 className="h-4 w-4 text-green-600" />
          <AlertDescription className="text-green-800">{successMessage}</AlertDescription>
        </Alert>
      )}

      {/* Teachers Table */}
      <Card>
        <CardContent className="pt-6">
          {filteredTeachers.length === 0 ? (
            <div className="text-center py-12 text-gray-500">
              <Users className="w-12 h-12 mx-auto mb-3 opacity-50" />
              <p>Tidak ada pengajar yang sesuai dengan pencarian</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead className="w-12">#</TableHead>
                    <TableHead>Nama</TableHead>
                    <TableHead>Mata Pelajaran</TableHead>
                    <TableHead className="text-center">Jadwal Mengajar</TableHead>
                    <TableHead className="text-right">Aksi</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredTeachers.map((teacher, index) => {
                    const initials = teacher.name
                      .split(" ")
                      .slice(0, 2)
                      .map((n) => n[0])
                      .join("");
                    const scheduleCount = teacher.schedules?.length || 0;

                    return (
                      <TableRow key={teacher.id}>
                        <TableCell>{index + 1}</TableCell>
                        <TableCell>
                          <div className="flex items-center gap-3">
                            <Avatar className="w-8 h-8">
                              <AvatarFallback className="text-xs bg-emerald-600 text-white">{initials}</AvatarFallback>
                            </Avatar>
                            <span>{teacher.name}</span>
                          </div>
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline" className="bg-blue-50 text-blue-700">
                            {teacher.subject}
                          </Badge>
                        </TableCell>
                        <TableCell className="text-center">
                          <Badge variant={scheduleCount > 0 ? "default" : "outline"} className={scheduleCount > 0 ? "bg-emerald-600" : ""}>
                            {scheduleCount > 0 ? `${scheduleCount} kelas` : "Belum dijadwalkan"}
                          </Badge>
                        </TableCell>
                        <TableCell className="text-right">
                          <div className="flex items-center justify-end gap-2">
                            <Button variant="outline" size="sm" onClick={() => openEditDialog(teacher)}>
                              <Edit className="w-4 h-4 mr-1" />
                              Edit
                            </Button>
                            <Button variant="outline" size="sm" onClick={() => openDeleteDialog(teacher)} className="text-red-600 hover:text-red-700 hover:bg-red-50">
                              <Trash2 className="w-4 h-4 mr-1" />
                              Hapus
                            </Button>
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

      {/* Add Dialog */}
      <Dialog open={isAddDialogOpen} onOpenChange={setIsAddDialogOpen}>
        <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <UserPlus className="w-5 h-5 text-emerald-600" />
              Tambah Pengajar Baru
            </DialogTitle>
            <DialogDescription>Masukkan data pengajar baru dan tentukan jadwal mengajarnya</DialogDescription>
          </DialogHeader>
          <div className="space-y-4 py-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="add-name">Nama Lengkap</Label>
                <Input id="add-name" placeholder="Contoh: Ustadz Ahmad Fauzi" value={formData.name} onChange={(e) => setFormData({ ...formData, name: e.target.value })} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="add-subject">Mata Pelajaran</Label>
                <Input id="add-subject" placeholder="Contoh: Tahfidz Quran" value={formData.subject} onChange={(e) => setFormData({ ...formData, subject: e.target.value })} />
              </div>
            </div>

            <div className="border-t pt-4">
              <ScheduleSelector />
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setIsAddDialogOpen(false)}>
              Batal
            </Button>
            <Button onClick={handleAdd} className="bg-emerald-600 hover:bg-emerald-700" disabled={!formData.name || !formData.subject}>
              Simpan
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Edit Dialog */}
      <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
        <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <Edit className="w-5 h-5 text-blue-600" />
              Edit Data Pengajar
            </DialogTitle>
            <DialogDescription>Perbarui data pengajar dan jadwal mengajarnya</DialogDescription>
          </DialogHeader>
          <div className="space-y-4 py-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="edit-name">Nama Lengkap</Label>
                <Input id="edit-name" placeholder="Contoh: Ustadz Ahmad Fauzi" value={formData.name} onChange={(e) => setFormData({ ...formData, name: e.target.value })} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="edit-subject">Mata Pelajaran</Label>
                <Input id="edit-subject" placeholder="Contoh: Tahfidz Quran" value={formData.subject} onChange={(e) => setFormData({ ...formData, subject: e.target.value })} />
              </div>
            </div>

            <div className="border-t pt-4">
              <ScheduleSelector />
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setIsEditDialogOpen(false)}>
              Batal
            </Button>
            <Button onClick={handleEdit} className="bg-blue-600 hover:bg-blue-700" disabled={!formData.name || !formData.subject}>
              Perbarui
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Delete Confirmation Dialog */}
      <AlertDialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Konfirmasi Hapus</AlertDialogTitle>
            <AlertDialogDescription>
              Apakah Anda yakin ingin menghapus <strong>{selectedTeacher?.name}</strong>? Semua jadwal mengajar ({selectedTeacher?.schedules?.length || 0} kelas) akan ikut terhapus. Tindakan ini tidak dapat dibatalkan.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Batal</AlertDialogCancel>
            <AlertDialogAction onClick={handleDelete} className="bg-red-600 hover:bg-red-700">
              Hapus
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
