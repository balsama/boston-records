import sqlite3
import pandas as pd
import matplotlib.pyplot as plt

# Database path
db_path = '../data/exports/attempt-24.db'

# SQL query to fetch ticket data
query = '''
SELECT ticket_issue_date, issuing_agency, COUNT(*) AS ticket_count
FROM tickets
GROUP BY ticket_issue_date, issuing_agency
ORDER BY ticket_issue_date, issuing_agency;
'''

# Connect to the SQLite database
conn = sqlite3.connect(db_path)

# Execute the query and fetch the data into a DataFrame
df = pd.read_sql_query(query, conn)

# Close the database connection
conn.close()

# Convert 'ticket_issue_date' to datetime format
df['ticket_issue_date'] = pd.to_datetime(df['ticket_issue_date'])

# Set 'ticket_issue_date' as index
df.set_index('ticket_issue_date', inplace=True)

# Resample by week and aggregate the ticket counts
df_weekly = df.groupby('issuing_agency').resample('W').sum().reset_index()

# Pivot the DataFrame for stacked bar chart
df_pivot = df_weekly.pivot(index='ticket_issue_date', columns='issuing_agency', values='ticket_count').fillna(0)

# Plotting
plt.figure(figsize=(14, 8))
df_pivot.plot(kind='bar', stacked=True, figsize=(14, 8))

# Customizing the plot
plt.title('Number of Tickets Issued per Week by Issuing Agency')
plt.xlabel('Week')
plt.ylabel('Number of Tickets')
plt.legend(title='Issuing Agency')
plt.grid(True)
plt.tight_layout()

# Show the plot
plt.show()
