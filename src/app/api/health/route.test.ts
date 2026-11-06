import { beforeEach, describe, expect, it, vi } from "vitest";

vi.mock("@/lib/prisma", () => ({
  prisma: {
    $queryRaw: vi.fn()
  }
}));

import { GET } from "./route";
import { prisma } from "@/lib/prisma";

describe("GET /api/health", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("returns the healthy database status", async () => {
    vi.mocked(prisma.$queryRaw).mockResolvedValue([]);

    const response = await GET();

    expect(response.status).toBe(200);
    await expect(response.json()).resolves.toEqual({ status: "ok", database: "ok" });
  });

  it("returns an error when the database query fails", async () => {
    vi.mocked(prisma.$queryRaw).mockRejectedValue(new Error("Database unavailable"));

    const response = await GET();

    expect(response.status).toBe(500);
    await expect(response.json()).resolves.toEqual({ status: "error", database: "error" });
  });
});
