import { useState, useEffect } from "react";
import { AttendanceHeader } from "./components/AttendanceHeader";
import { DutyOfficerView } from "./components/DutyOfficerView";
// Local AdminView component to avoid missing module import.
// Replace this with your real ./components/AdminView.tsx when available.
function AdminView({ teachers, onUpdateTeachers, onLogout }: { teachers: Teacher[]; onUpdateTeachers: (t: Teacher[]) => void; onLogout: () => void }) {
  return (
    <div>
      <h2 className="text-gray-900 mb-4">Admin Panel</h2>
      <p className="text-gray-600"></p>
      <div className="mt-4">
        <button
          onClick={() => {
            // Example: call onUpdateTeachers with the same list (no-op)
            onUpdateTeachers(teachers);
          }}
          className="mr-2 px-3 py-2 bg-blue-600 text-white rounded"
        >
          Save Changes
        </button>
        <button onClick={onLogout} className="px-3 py-2 bg-gray-200 rounded">
          Logout
        </button>
      </div>
    </div>
  );
}
import { LoginPage } from "./components/LoginPage";
import { Button } from "./components/ui/button";
import { allTeachers as initialTeachers } from "./data/mockData";
import { ClipboardCheck, Shield } from "lucide-react";
import { AttendanceStatus, PrayerTime, Teacher } from "./types/attendance";

type ViewMode = "user" | "duty-officer" | "admin-login" | "admin-panel";

export default function App() {
  // Default to user-facing view
  const [viewMode, setViewMode] = useState<ViewMode>("user");
  const [isAdminLoggedIn, setIsAdminLoggedIn] = useState(false);
  const [teachers, setTeachers] = useState<Teacher[]>(initialTeachers);

  // Check if admin is already logged in (from localStorage)
  useEffect(() => {
    const adminSession = localStorage.getItem("adminSession");
    if (adminSession) {
      setIsAdminLoggedIn(true);
      if (viewMode === "admin-login") {
        setViewMode("admin-panel");
      }
    }
  }, []);

  const handleMarkAttendance = (teacherId: string, prayerTime: PrayerTime, status: AttendanceStatus) => {
    // In a real app, this would save to a database
    console.log("Marking attendance:", { teacherId, prayerTime, status });
  };

  const handleLogin = (username: string, password: string) => {
    // Store session in localStorage
    localStorage.setItem("adminSession", JSON.stringify({ username, timestamp: Date.now() }));
    setIsAdminLoggedIn(true);
    setViewMode("admin-panel");
  };

  const handleLogout = () => {
    localStorage.removeItem("adminSession");
    setIsAdminLoggedIn(false);
    setViewMode("user");
  };

  const handleAdminAccess = () => {
    if (isAdminLoggedIn) {
      setViewMode("admin-panel");
    } else {
      setViewMode("admin-login");
    }
  };

  const handleUpdateTeachers = (updatedTeachers: Teacher[]) => {
    setTeachers(updatedTeachers);
    // In a real app, this would update the database
  };

  // Show login page if trying to access admin
  if (viewMode === "admin-login") {
    return <LoginPage onLogin={handleLogin} />;
  }

  // Show admin panel if logged in
  if (viewMode === "admin-panel" && isAdminLoggedIn) {
    return <AdminView teachers={teachers} onUpdateTeachers={handleUpdateTeachers} onLogout={handleLogout} />;
  }

  // Default: User View (read-only)
  return (
    <div className="min-h-screen bg-gray-50">
      <AttendanceHeader />

      <div className="container mx-auto px-6 py-8">
        <div className="space-y-8">
          {/* View Mode Selector (User-facing only) */}
          <div className="flex gap-3">
            <Button variant="default" className="flex items-center gap-2" onClick={() => setViewMode("user")}>
              <ClipboardCheck className="w-4 h-4" />
              Tampilan Pengguna
            </Button>

            {/* Optional: small Admin access (kept minimal, hidden to regular users)
                Remove the button below if you want to hide admin access entirely. */}
            <button onClick={handleAdminAccess} className="text-sm text-gray-500 underline ml-3">
              Admin (masuk)
            </button>
          </div>

          {/* User View (read-only) */}
          <div>
            <h2 className="text-gray-900 mb-4">Tampilan Pengguna</h2>
            {/* Reuse the DutyOfficerView for listing; it should render the teacher list.
                If DutyOfficerView has editing controls you can create a read-only component instead.
            */}
            <DutyOfficerView teachers={teachers} onMarkAttendance={handleMarkAttendance} />
          </div>
        </div>
      </div>
    </div>
  );
}
