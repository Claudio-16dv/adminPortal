"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { ToastContainer, toast } from "react-toastify";
import styles from "../page.module.css";
import 'react-toastify/dist/ReactToastify.css';

export default function RegisterPage() {
  const router = useRouter();
  const [form, setForm] = useState({ name: "", email: "", password: "" });

  const handleChange = (field, value) => {
    setForm(prev => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!form.name || !form.email || !form.password) {
      toast.error("Preencha todos os campos.");
      return;
    }

    try {
      const res = await fetch("http://localhost:8000/auth/create", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(form),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.message || data.error || "Erro ao cadastrar.");
      }

      toast.success("Usuário cadastrado com sucesso!");
      router.push("/");
    } catch (err) {
      toast.error(err.message || "Erro inesperado.");
    }
  };

  return (
    <div className={styles.container}>
      <div className={styles.card}>
        <h2 className={styles.title}>Cadastro</h2>
        <form onSubmit={handleSubmit} className={styles.form}>
          <input className={styles.input} type="text" placeholder="Nome" value={form.name} onChange={(e) => handleChange("name", e.target.value)} />
          <input className={styles.input} type="email" placeholder="Email" value={form.email} onChange={(e) => handleChange("email", e.target.value)} />
          <input className={styles.input} type="password" placeholder="Senha" value={form.password} onChange={(e) => handleChange("password", e.target.value)} />
          <button type="submit" className={styles.button}>Criar Conta</button>
        </form>
        <p className={styles.linkWrapper}>
          <a href="/" className={styles.linkBack}>Voltar</a>
        </p>
      </div>
      <ToastContainer position="top-right" autoClose={3000} hideProgressBar />
    </div>
  );
}
