SELECT ArticleCommentID AS DB_ID
FROM article_comments
WHERE CategoryKey = '[CategoryKey]'
ORDER BY Date ASC, Time ASC
LIMIT [Start],[EntriesCount];