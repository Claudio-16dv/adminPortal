"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { ToastContainer, toast } from "react-toastify";
import styles from "./page.module.css";
import 'react-toastify/dist/ReactToastify.css';

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const handleLogin = async (e) => {
    e.preventDefault();
    if (!email || !password) {
      toast.error("Preencha todos os campos.");
      return;
    }

    try {
      const res = await fetch("http://localhost:8000/auth/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({ email, password }),
      });

      const data = await res.json();
      if (!res.ok) {
        throw new Error(data.error || data.message || "Erro ao fazer login.");
      }

      localStorage.setItem("token", data.token);
      toast.success("Login realizado com sucesso!");
      router.push("/home");
    } catch (err) {
      toast.error(err.message || "Erro inesperado.");
    }
  };

  return (
    <div className={styles.container}>
      <div className={styles.card}>
        <h2 className={styles.title}>Login</h2>
        <form onSubmit={handleLogin} className={styles.form}>
          <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} className={styles.input} placeholder="Email" />
          <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} className={styles.input} placeholder="Senha" />
          <button type="submit" className={styles.button}>Entrar</button>
        </form>
        <p className={styles.linkWrapper}>
          <a href="/register_user" className={styles.linkBack}>Cadastre-se</a>
        </p>
      </div>
      <ToastContainer position="top-right" autoClose={3000} hideProgressBar />
    </div>
  );
}
