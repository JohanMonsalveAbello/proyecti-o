SELECT
    COALESCE(parent.name, 'Sin categoría') AS category_name,
    AVG(last_edi.score) AS edi_score_promedio,
    COUNT(DISTINCT w.id) AS total_workers,
    COUNT(DISTINCT CASE 
        WHEN w.id IN (
            SELECT t.worker_id FROM bids b 
            JOIN threads t ON t.bid_id = b.id
            WHERE b.created >= '2025-10-01'
        ) THEN w.id 
    END) AS workers_bideando,
    ROUND(COUNT(DISTINCT CASE 
        WHEN w.id IN (
            SELECT t.worker_id FROM bids b 
            JOIN threads t ON t.bid_id = b.id
            WHERE b.created >= '2025-10-01'
        ) THEN w.id 
    END) * 100.0 / COUNT(DISTINCT w.id), 2) AS pct_bideando,
    COUNT(DISTINCT CASE 
        WHEN w.id NOT IN (
            SELECT t.worker_id FROM bids b 
            JOIN threads t ON t.bid_id = b.id
            WHERE b.created >= '2025-10-01'
        ) THEN w.id 
    END) AS workers_no_bideando,
    ROUND(COUNT(DISTINCT CASE 
        WHEN w.id NOT IN (
            SELECT t.worker_id FROM bids b 
            JOIN threads t ON t.bid_id = b.id
            WHERE b.created >= '2025-10-01'
        ) THEN w.id 
    END) * 100.0 / COUNT(DISTINCT w.id), 2) AS pct_no_bideando
FROM workers w
JOIN companies c ON c.worker_id = w.id
JOIN users u ON u.id = c.user_id
JOIN (
    SELECT wosh.worker_id, wosh.score
    FROM worker_onboarding_score_history wosh
    JOIN (
        SELECT worker_id, MAX(calculated_at) AS max_calc
        FROM worker_onboarding_score_history
        WHERE score IS NOT NULL AND score <> 0
        GROUP BY worker_id
    ) best ON best.worker_id = wosh.worker_id
          AND best.max_calc = wosh.calculated_at
    WHERE wosh.score IS NOT NULL AND wosh.score <> 0
) last_edi ON last_edi.worker_id = w.id
LEFT JOIN worker_skills ws ON ws.worker_id = w.id
LEFT JOIN skills sub ON sub.id = ws.skill_id
LEFT JOIN skills parent ON parent.id = sub.parent_id
WHERE w.created >= '2025-10-01'
GROUP BY parent.name
ORDER BY total_workers DESC;






WITH base AS (
  SELECT
    CASE
      WHEN c.completeness BETWEEN 0  AND 10  THEN '0-10%'
      WHEN c.completeness BETWEEN 11 AND 20  THEN '10-20%'
      WHEN c.completeness BETWEEN 21 AND 30  THEN '20-30%'
      WHEN c.completeness BETWEEN 31 AND 40  THEN '30-40%'
      WHEN c.completeness BETWEEN 41 AND 50  THEN '40-50%'
      WHEN c.completeness BETWEEN 51 AND 60  THEN '50-60%'
      WHEN c.completeness BETWEEN 61 AND 70  THEN '60-70%'
      WHEN c.completeness BETWEEN 71 AND 80  THEN '70-80%'
      WHEN c.completeness BETWEEN 81 AND 90  THEN '80-90%'
      WHEN c.completeness BETWEEN 91 AND 100 THEN '90-100%'
      ELSE 'Sin datos'
    END AS bucket,
    CASE
      WHEN c.completeness BETWEEN 0  AND 10  THEN 0
      WHEN c.completeness BETWEEN 11 AND 20  THEN 10
      WHEN c.completeness BETWEEN 21 AND 30  THEN 20
      WHEN c.completeness BETWEEN 31 AND 40  THEN 30
      WHEN c.completeness BETWEEN 41 AND 50  THEN 40
      WHEN c.completeness BETWEEN 51 AND 60  THEN 50
      WHEN c.completeness BETWEEN 61 AND 70  THEN 60
      WHEN c.completeness BETWEEN 71 AND 80  THEN 70
      WHEN c.completeness BETWEEN 81 AND 90  THEN 80
      WHEN c.completeness BETWEEN 91 AND 100 THEN 90
      ELSE 999
    END AS bucket_order,
    COUNT(*) AS total_usuarios
  FROM users u
  JOIN companies c ON c.user_id = u.id
  JOIN workers w ON w.id = c.worker_id
  WHERE u.created >= '2025-10-01'
    AND w.onboarding_position IN ('how_it_works', 'workana_test', 'onboarded')
    AND w.id IN (
      SELECT DISTINCT t.worker_id
      FROM bids b
      JOIN threads t ON t.bid_id = b.id
      WHERE b.created >= '2025-10-01'
    )
  GROUP BY 1, 2
),

final AS (
  SELECT
    bucket,
    bucket_order,
    total_usuarios,
    ROUND(total_usuarios * 1.0 / SUM(total_usuarios) OVER(), 4) AS porcentaje,
    ROUND(
      SUM(total_usuarios) OVER (
        ORDER BY bucket_order
        ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
      ) * 1.0 / SUM(total_usuarios) OVER(),
      4
    ) AS porcentaje_acumulado
  FROM base
)

SELECT 
  bucket,
  total_usuarios,
  porcentaje * 100 AS porcentaje,
  porcentaje_acumulado * 100 AS porcentaje_acumulado
FROM final
ORDER BY bucket_order;