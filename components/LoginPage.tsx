import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "./ui/card";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { Label } from "./ui/label";
import { Alert, AlertDescription } from "./ui/alert";
import { Lock, User, AlertCircle, BookOpen } from "lucide-react";

interface LoginPageProps {
  onLogin: (username: string, password: string) => void;
}

export function LoginPage({ onLogin }: LoginPageProps) {
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setIsLoading(true);

    // Simulate API call
    setTimeout(() => {
      // Demo credentials: admin/admin123
      if (username === "admin" && password === "admin123") {
        onLogin(username, password);
      } else {
        setError("Username atau password salah!");
      }
      setIsLoading(false);
    }, 500);
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        {/* Header */}
        <div className="text-center mb-8">
          <div className="inline-flex items-center justify-center w-16 h-16 bg-emerald-600 rounded-full mb-4">
            <BookOpen className="w-8 h-8 text-white" />
          </div>
          <h1 className="text-gray-900 mb-2">Sistem Absensi Madrasah</h1>
          <p className="text-gray-600">Panel Administrator</p>
        </div>

        {/* Login Card */}
        <Card className="shadow-lg">
          <CardHeader>
            <CardTitle>Login Admin</CardTitle>
            <CardDescription>Masukkan kredensial Anda untuk mengakses panel admin</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              {error && (
                <Alert variant="destructive">
                  <AlertCircle className="h-4 w-4" />
                  <AlertDescription>{error}</AlertDescription>
                </Alert>
              )}

              <div className="space-y-2">
                <Label htmlFor="username">Username</Label>
                <div className="relative">
                  <User className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                  <Input id="username" type="text" placeholder="Masukkan username" value={username} onChange={(e) => setUsername(e.target.value)} className="pl-10" required />
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="password">Password</Label>
                <div className="relative">
                  <Lock className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                  <Input id="password" type="password" placeholder="Masukkan password" value={password} onChange={(e) => setPassword(e.target.value)} className="pl-10" required />
                </div>
              </div>

              <Button type="submit" className="w-full bg-emerald-600 hover:bg-emerald-700" disabled={isLoading}>
                {isLoading ? "Memproses..." : "Masuk"}
              </Button>
            </form>

            <div className="mt-6 p-4 bg-blue-50 rounded-lg">
              <p className="text-sm text-blue-800 mb-2">Demo Kredensial:</p>
              <p className="text-xs text-blue-600">
                Username: <strong>admin</strong>
              </p>
              <p className="text-xs text-blue-600">
                Password: <strong>admin123</strong>
              </p>
            </div>
          </CardContent>
        </Card>

        <p className="text-center text-sm text-gray-600 mt-6">© 2024 Madrasah Diniyyah. All rights reserved.</p>
      </div>
    </div>
  );
}
