"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import styles from "../../page.module.css";
import { toast } from "react-toastify";

export default function ClientsView() {
  const router = useRouter();
  const [clients, setClients] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [openId, setOpenId] = useState(null);
  const [details, setDetails] = useState({});
  const [modalClientId, setModalClientId] = useState(null);

  const onlyDigits = (s = "") => s.replace(/\D/g, "");

  const formatCpf = (s = "") => {
    const d = onlyDigits(s).slice(0, 11);
    if (!d) return "-";
    const p1 = d.slice(0, 3), p2 = d.slice(3, 6), p3 = d.slice(6, 9), p4 = d.slice(9, 11);
    if (d.length <= 3) return p1;
    if (d.length <= 6) return `${p1}.${p2}`;
    if (d.length <= 9) return `${p1}.${p2}.${p3}`;
    return `${p1}.${p2}.${p3}-${p4}`;
  };

  const formatPhone = (s = "") => {
    const d = onlyDigits(s).slice(0, 11);
    if (!d) return "-";
    const ddd = d.slice(0, 2);
    if (d.length <= 2) return `(${ddd}`;
    if (d.length <= 7) return `(${ddd}) ${d.slice(2)}`;
    return `(${ddd}) ${d.slice(2, 7)}-${d.slice(7)}`;
  };

  const formatCep = (s = "") => {
    const d = onlyDigits(s).slice(0, 8);
    if (!d) return "";
    if (d.length <= 5) return d;
    return `${d.slice(0, 5)}-${d.slice(5)}`;
  };

  const addressLines = (addr = {}) => {
    const street = addr.street || "";
    const number = addr.number || "";
    const neighborhood = addr.neighborhood || "";
    const city = addr.city || "";
    const state = addr.state || "";
    const cep = formatCep(addr.zip_code || "");

    const line1 =
      [street, number ? `, ${number}` : ""].filter(Boolean).join("") +
      (neighborhood ? ` - ${neighborhood}` : "");

    const line2 =
      [city, state ? ` - ${state}` : ""].filter(Boolean).join("") +
      (cep ? `, ${cep}` : "");

    return { line1: line1 || "-", line2 };
  };

  const fetchClients = async () => {
    try {
      const res = await fetch("http://localhost:8000/clients/list", {
        credentials: "include",
        headers: { "Content-Type": "application/json" },
      });
      if (res.status === 401) {
        router.push("/");
        return;
      }
      const data = await res.json();
      setClients(data);

      const map = {};
      for (const c of data) map[c.id] = c;
      setDetails(map);
    } catch {
      setError("Falha ao buscar clientes");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchClients();
  }, [router]);

  const toggleClient = async (id) => {
    if (openId === id) {
      setOpenId(null);
      return;
    }

    if (!details[id] || !details[id].addresses) {
      const res = await fetch(`http://localhost:8000/clients/edit/${id}`, {
        credentials: "include",
        headers: { "Content-Type": "application/json" },
      });
      if (res.status === 401) {
        router.push("/");
        return;
      }
      const data = await res.json();
      const safeData = {
        id: data.id,
        name: data.name ?? "",
        birthdate: data.birthdate ?? "",
        cpf: data.cpf ?? "",
        rg: data.rg ?? "",
        phone: data.phone ?? "",
        addresses: Array.isArray(data.addresses) ? data.addresses : [],
      };
      setDetails((prev) => ({ ...prev, [id]: safeData }));
    }

    setOpenId(id);
  };

  const handleDeleteClient = (clientId) => setModalClientId(clientId);

  const confirmDeleteClient = async () => {
    const clientId = modalClientId;
    if (!clientId) return;
    try {
      const res = await fetch(`http://localhost:8000/clients/delete/${clientId}`, {
        method: "DELETE",
        credentials: "include",
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data.message || data.error || "Erro ao deletar o cliente.");

      toast.success("Cliente deletado com sucesso!");
      setClients((prev) => prev.filter((c) => c.id !== clientId));
      setOpenId(null);
      setModalClientId(null);
    } catch (err) {
      toast.error(err.message || "Erro ao deletar cliente.");
    }
  };

  if (loading) return <p className={styles.loadingText}>Carregando…</p>;
  if (error) return <p className={styles.errorMsg}>{error}</p>;

  return (
    <div className={styles.wrapperMain}>
      <div className={styles.clientContainer}>
        <h1 className={styles.headingMain}>Clientes</h1>

        {clients.length === 0 ? (
          <div className={styles.clientCard}>
            <p className={styles.emptyMsg}>
              Nenhum cliente cadastrado.
            </p>
            <button
              className={styles.btnSave}
              onClick={() => router.push("/cadastro_cliente")}
            >
              Cadastrar Novo Cliente
            </button>
          </div>
        ) : (
          clients.map((c) => (
            <div key={c.id} className={styles.clientCard}>
              <div className={styles.clientHeader}>
                <span>{c.name}</span>
                <button className={styles.btnToggle} onClick={() => toggleClient(c.id)}>
                  {openId === c.id ? "Fechar" : "Ver Dados"}
                </button>
              </div>

              {openId === c.id && details[c.id] && (
                <div className={styles.clientDetails}>
                  <div className={styles.viewRow}><strong>Nascimento:</strong> {details[c.id].birthdate || "-"}</div>
                  <div className={styles.viewRow}><strong>CPF:</strong> {formatCpf(details[c.id].cpf)}</div>
                  <div className={styles.viewRow}><strong>RG:</strong> {details[c.id].rg || "-"}</div>
                  <div className={styles.viewRow}><strong>Telefone:</strong> {formatPhone(details[c.id].phone)}</div>

                  <h3 className={styles.addressTitle}>Endereços</h3>
                  {Array.isArray(details[c.id].addresses) && details[c.id].addresses.length > 0 ? (
                    details[c.id].addresses.map((addr, idx) => {
                      const { line1, line2 } = addressLines(addr);
                      return (
                        <div key={idx} className={styles.addressWrapper}>
                          <div className={styles.addressHeader}>
                            <span>Endereço {idx + 1}</span>
                          </div>
                          <div className={styles.addressDetails}>
                            <div className={styles.addressLine}>{line1}</div>
                            {line2 && <div className={styles.addressLineMuted}>{line2}</div>}
                          </div>
                        </div>
                      );
                    })
                  ) : (
                    <div className={styles.addressWrapper}>
                      <div className={styles.addressDetails}>Nenhum endereço cadastrado.</div>
                    </div>
                  )}

                  <div className={styles.buttonGroup}>
                    <button
                      className={styles.btnAction}
                      onClick={() => {
                        sessionStorage.setItem("editClientId", String(c.id));
                        router.push("/editar");
                      }}
                    >
                      Editar
                    </button>
                    <button
                      className={styles.btnDanger}
                      onClick={() => handleDeleteClient(c.id)}
                    >
                      Deletar Cliente
                    </button>
                  </div>
                </div>
              )}
            </div>
          ))
        )}

        <button className={styles.menuBtn} onClick={() => router.push("/home")}>
          Voltar
        </button>
      </div>

      {modalClientId !== null && (
        <div className={styles.modalOverlay}>
          <div className={styles.modalBox}>
            <h2 className={styles.modalTitle}>Deseja mesmo excluir este cliente?</h2>
            <div className={styles.modalActions}>
              <button className={styles.btnModalConfirm} onClick={confirmDeleteClient}>
                Confirmar
              </button>
              <button className={styles.btnModalCancel} onClick={() => setModalClientId(null)}>
                Cancelar
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
