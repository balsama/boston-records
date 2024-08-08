import sqlite3
import pandas as pd
import matplotlib.pyplot as plt

# Database path
db_path = '../data/exports/attempt-24.db'

# SQL query to count the number of rows by day of the week and violation description
query = '''
SELECT strftime('%w', ticket_issue_date) AS day_of_week, violation_desc_long, COUNT(*) AS ticket_count
FROM tickets
GROUP BY day_of_week, violation_desc_long
ORDER BY day_of_week, violation_desc_long;
'''

# Connect to the SQLite database
conn = sqlite3.connect(db_path)

# Execute the query and fetch the data into a DataFrame
df = pd.read_sql_query(query, conn)

# Close the database connection
conn.close()

# Map numeric day of the week to names
day_map = {
    '0': 'Sunday',
    '1': 'Monday',
    '2': 'Tuesday',
    '3': 'Wednesday',
    '4': 'Thursday',
    '5': 'Friday',
    '6': 'Saturday'
}

# Convert numeric day of the week to names
df['day_of_week'] = df['day_of_week'].map(day_map)

# Pivot the DataFrame for stacked bar chart
df_pivot = df.pivot_table(index='day_of_week', columns='violation_desc_long', values='ticket_count', fill_value=0)

# Sort DataFrame by the day of the week
day_order = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
df_pivot = df_pivot.reindex(day_order)

# Plotting
plt.figure(figsize=(14, 8))
df_pivot.plot(kind='bar', stacked=True, figsize=(14, 8), colormap='tab20')

# Customizing the plot
plt.title('Number of Tickets Issued by Day of the Week and Violation Description')
plt.xlabel('Day of the Week')
plt.ylabel('Number of Tickets')
plt.xticks(rotation=45)
plt.legend(title='Violation Description', bbox_to_anchor=(1.05, 1), loc='upper left')
plt.grid(axis='y', linestyle='--', alpha=0.7)
plt.tight_layout()

# Show the plot
plt.show()
